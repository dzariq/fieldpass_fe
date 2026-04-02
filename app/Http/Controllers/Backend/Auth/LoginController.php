<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class LoginController extends Controller
{
    protected string $redirectTo = RouteServiceProvider::ADMIN_DASHBOARD;

    private const FIXED_COUNTRY_CODE = '+60';

    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if ($request->boolean('change_phone')) {
            $request->session()->forget([
                'admin_otp_phone_digits',
                'admin_otp_country_code',
                'admin_otp_phone_display',
                'admin_otp_sent',
            ]);

            return redirect()->route('admin.login');
        }

        return view('backend.auth.login', [
            'country_code' => self::FIXED_COUNTRY_CODE,
            'otp_sent' => (bool) session('admin_otp_sent', false),
            'pending_phone' => session('admin_otp_phone_display'),
        ]);
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_code' => 'required|in:+60',
            'phone' => ['required', 'string', 'regex:/^[0-9]+$/'],
        ]);

        $phoneDigits = preg_replace('/\D/', '', $validated['phone'] ?? '');
        $phoneDigits = $phoneDigits !== '' ? $phoneDigits : '';

        if ($phoneDigits === '') {
            return back()->withInput()->withErrors(['phone' => 'Please enter a valid phone number (digits only).']);
        }

        if ($this->shouldBypassOtpForSuperadminLogin($phoneDigits)) {
            return $this->completeSuperadminBypassLogin($request);
        }

        $countryCode = self::FIXED_COUNTRY_CODE;

        $url = config('services.n8n.login_otp_url');
        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'country_code' => $countryCode,
                    'phone' => $phoneDigits,
                ]);
        } catch (Throwable $e) {
            Log::error('Admin login-otp webhook failed', ['exception' => $e->getMessage()]);
            return back()->withInput()->withErrors(['phone' => 'Could not send OTP. Please try again. (' . $e->getMessage() . ')']);
        }

        if (!$this->webhookSucceeded($response)) {
            Log::warning('Admin login-otp webhook rejected', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->withInput()->withErrors([
                'phone' => 'OTP service returned an error (HTTP ' . $response->status() . '). ' . $this->truncateBody($response->body()),
            ]);
        }

        $request->session()->put([
            'admin_otp_phone_digits' => $phoneDigits,
            'admin_otp_country_code' => $countryCode,
            'admin_otp_phone_display' => $phoneDigits,
            'admin_otp_sent' => true,
        ]);

        return back()->with('success', 'OTP has been sent to your phone. Enter the code below.');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'country_code' => 'required|in:+60',
            'phone' => ['required', 'string', 'regex:/^[0-9]+$/'],
            'otp' => ['required', 'string', 'regex:/^[0-9]+$/', 'min:4', 'max:10'],
        ]);

        $phoneDigits = preg_replace('/\D/', '', $request->input('phone', ''));
        $sessionPhone = (string) $request->session()->get('admin_otp_phone_digits', '');
        $sessionCc = (string) $request->session()->get('admin_otp_country_code', '');

        if ($sessionPhone === '' || $sessionCc === '' || $phoneDigits !== $sessionPhone || $request->input('country_code') !== $sessionCc) {
            return back()->withInput()->withErrors([
                'otp' => 'Session expired or phone mismatch. Request a new OTP.',
            ]);
        }

        $url = config('services.n8n.verify_otp_url');
        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'phone' => $sessionPhone,
                    'otp' => preg_replace('/\D/', '', $request->input('otp', '')),
                ]);
        } catch (Throwable $e) {
            Log::error('Admin verify-otp webhook failed', ['exception' => $e->getMessage()]);

            return $this->redirectOtpVerifyFailed();
        }

        if (!$this->webhookVerifySucceeded($response)) {
            Log::warning('Admin verify-otp webhook rejected', [
                'http_status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
            ]);

            return $this->redirectOtpVerifyFailed();
        }

        $admin = $this->findAdminByPhoneAndCountryCode($sessionCc, $sessionPhone);
        if (!$admin) {
            $request->session()->forget(['admin_otp_phone_digits', 'admin_otp_country_code', 'admin_otp_phone_display', 'admin_otp_sent']);

            return back()->withInput()->withErrors([
                'phone' => 'No admin account found for this phone number. Please contact your association administrator.',
            ]);
        }

        if ($admin->status !== 'ACTIVE') {
            return back()->withInput()->withErrors([
                'phone' => 'Your account is not active. Please contact support.',
            ]);
        }

        $request->session()->forget(['admin_otp_phone_digits', 'admin_otp_country_code', 'admin_otp_phone_display', 'admin_otp_sent']);
        $request->session()->regenerate();

        Auth::guard('admin')->login($admin, $request->boolean('remember'));

        return redirect()->intended($this->redirectPath())->with('success', 'Successfully logged in.');
    }

    protected function redirectPath(): string
    {
        return $this->redirectTo;
    }

    /**
     * Always return to the named login route so flash messages render (back() can leave the OTP step).
     */
    private function redirectOtpVerifyFailed(): RedirectResponse
    {
        return redirect()
            ->route('admin.login')
            ->withInput()
            ->with('error', 'OTP verification failed')
            ->with('otp_verify_alert', 'OTP verification failed');
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Login phone is national digits only (+60 is fixed in the UI). Match admins.phone to that value:
     * Direct DB match first (string or int column), then digit-exact, then Malaysia-normalized (601… / 01…).
     * country_code is not used for matching on the +60 flow.
     */
    private function findAdminByPhoneAndCountryCode(string $countryCode, string $phoneDigits): ?Admin
    {
        $inputDigits = self::digitsOnly($phoneDigits);
        $normalizedInput = self::normalizeMalaysiaPhoneDigits($phoneDigits);
        if ($inputDigits === '' && $normalizedInput === '') {
            return null;
        }

        $base = Admin::query()->whereRaw('LOWER(TRIM(COALESCE(status, ?))) = ?', ['', 'active']);

        if ($countryCode !== self::FIXED_COUNTRY_CODE && $countryCode !== '60') {
            $base->where('country_code', $countryCode);
        }

        // Fast path: column equals what the user typed (VARCHAR or INT).
        if ($inputDigits !== '') {
            $direct = (clone $base)
                ->whereNotNull('phone')
                ->where(function ($q) use ($inputDigits) {
                    $q->where('phone', $inputDigits);
                    if (ctype_digit($inputDigits)) {
                        $q->orWhere('phone', (int) $inputDigits);
                    }
                })
                ->first();
            if ($direct !== null) {
                return $direct;
            }
        }

        $query = (clone $base)
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        foreach ($query->get() as $admin) {
            $stored = trim((string) $admin->phone);
            $storedDigits = self::digitsOnly($stored);
            if ($inputDigits !== '' && $storedDigits !== '' && hash_equals($storedDigits, $inputDigits)) {
                return $admin;
            }
            $storedNorm = self::normalizeMalaysiaPhoneDigits($stored);
            if ($normalizedInput !== '' && $storedNorm !== '' && hash_equals($storedNorm, $normalizedInput)) {
                return $admin;
            }
        }

        return null;
    }

    private static function digitsOnly(string $raw): string
    {
        return preg_replace('/\D/', '', $raw) ?? '';
    }

    /**
     * National mobile digits for comparison (e.g. 186663282). Strips Malaysia country code 60 and leading zeros.
     */
    private static function normalizeMalaysiaPhoneDigits(string $raw): string
    {
        $d = self::digitsOnly($raw);
        if ($d === '') {
            return '';
        }
        if (str_starts_with($d, '60') && strlen($d) >= 11) {
            $d = substr($d, 2);
        }

        return ltrim($d, '0');
    }

    private function webhookSucceeded(\Illuminate\Http\Client\Response $response): bool
    {
        if (!$response->successful()) {
            return false;
        }
        $json = $response->json();
        if (!is_array($json)) {
            return true;
        }
        if (array_key_exists('success', $json) && $json['success'] === false) {
            return false;
        }
        if (array_key_exists('success', $json)) {
            return filter_var($json['success'], FILTER_VALIDATE_BOOLEAN);
        }
        if (array_key_exists('ok', $json)) {
            return filter_var($json['ok'], FILTER_VALIDATE_BOOLEAN);
        }

        return true;
    }

    /**
     * n8n verify-otp must return JSON with "status": 1 to allow login.
     * Supports both { "status": 1 } and [{ "status": 1 }] (n8n often returns an array).
     */
    private function webhookVerifySucceeded(\Illuminate\Http\Client\Response $response): bool
    {
        if (!$response->successful()) {
            return false;
        }

        return $this->verifyOtpStatusFromPayload($response->json()) === 1;
    }

    /**
     * @return int|null Parsed status code, or null if missing / invalid shape.
     */
    private function verifyOtpStatusFromPayload(mixed $json): ?int
    {
        if (!is_array($json)) {
            return null;
        }
        if (array_key_exists('status', $json)) {
            return (int) $json['status'];
        }
        if (array_is_list($json) && isset($json[0]) && is_array($json[0]) && array_key_exists('status', $json[0])) {
            return (int) $json[0]['status'];
        }

        return null;
    }

    private function truncateBody(string $body, int $max = 200): string
    {
        $body = trim($body);
        if (strlen($body) <= $max) {
            return $body;
        }

        return substr($body, 0, $max) . '…';
    }

    private function shouldBypassOtpForSuperadminLogin(string $phoneDigits): bool
    {
        if (!$this->isSuperadminOtpBypassAllowed()) {
            return false;
        }

        return $phoneDigits === $this->superadminOtpBypassPhoneDigits();
    }

    private function isSuperadminOtpBypassAllowed(): bool
    {
        $explicit = config('services.admin.allow_superadmin_otp_bypass');
        if ($explicit !== null && $explicit !== '') {
            return filter_var($explicit, FILTER_VALIDATE_BOOLEAN);
        }

        return app()->environment('local');
    }

    private function superadminOtpBypassPhoneDigits(): string
    {
        $digits = preg_replace('/\D/', '', (string) config('services.admin.superadmin_otp_bypass_phone', '9999999999'));

        return $digits !== '' ? $digits : '9999999999';
    }

    private function resolveSuperadminForBypass(): ?Admin
    {
        $byUsername = Admin::query()
            ->where('username', 'superadmin')
            ->where('status', 'ACTIVE')
            ->first();

        if ($byUsername) {
            return $byUsername;
        }

        return Admin::query()
            ->role('superadmin')
            ->where('status', 'ACTIVE')
            ->first();
    }

    private function completeSuperadminBypassLogin(Request $request): RedirectResponse
    {
        $admin = $this->resolveSuperadminForBypass();
        if (!$admin) {
            return back()->withInput()->withErrors([
                'phone' => 'Superadmin account not found. Run database seeders or create username "superadmin".',
            ]);
        }

        Log::warning('Admin OTP bypass login', [
            'admin_id' => $admin->id,
            'username' => $admin->username,
            'ip' => $request->ip(),
        ]);

        $request->session()->forget([
            'admin_otp_phone_digits',
            'admin_otp_country_code',
            'admin_otp_phone_display',
            'admin_otp_sent',
        ]);
        $request->session()->regenerate();

        Auth::guard('admin')->login($admin, false);

        return redirect()->intended($this->redirectPath())->with('success', 'Successfully logged in.');
    }
}
