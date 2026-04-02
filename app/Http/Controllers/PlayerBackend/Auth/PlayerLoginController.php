<?php

declare(strict_types=1);

namespace App\Http\Controllers\PlayerBackend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PlayerLoginController extends Controller
{
    protected string $redirectTo = RouteServiceProvider::PLAYER_DASHBOARD;

    private const FIXED_COUNTRY_CODE = '+60';

    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if ($request->boolean('change_phone')) {
            $request->session()->forget([
                'player_otp_phone_digits',
                'player_otp_country_code',
                'player_otp_phone_display',
                'player_otp_sent',
            ]);

            return redirect()->route('player.login');
        }

        return view('playerbackend.auth.login', [
            'country_code' => self::FIXED_COUNTRY_CODE,
            'otp_sent' => (bool) session('player_otp_sent', false),
            'pending_phone' => session('player_otp_phone_display'),
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
            Log::error('Player login-otp webhook failed', ['exception' => $e->getMessage()]);

            return back()->withInput()->withErrors(['phone' => 'Could not send OTP. Please try again. (' . $e->getMessage() . ')']);
        }

        if (!$this->webhookSucceeded($response)) {
            Log::warning('Player login-otp webhook rejected', ['status' => $response->status(), 'body' => $response->body()]);

            return back()->withInput()->withErrors([
                'phone' => 'OTP service returned an error (HTTP ' . $response->status() . '). ' . $this->truncateBody($response->body()),
            ]);
        }

        $request->session()->put([
            'player_otp_phone_digits' => $phoneDigits,
            'player_otp_country_code' => $countryCode,
            'player_otp_phone_display' => $phoneDigits,
            'player_otp_sent' => true,
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
        $sessionPhone = (string) $request->session()->get('player_otp_phone_digits', '');
        $sessionCc = (string) $request->session()->get('player_otp_country_code', '');

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
            Log::error('Player verify-otp webhook failed', ['exception' => $e->getMessage()]);

            return $this->redirectOtpVerifyFailed();
        }

        if (!$this->webhookVerifySucceeded($response)) {
            Log::warning('Player verify-otp webhook rejected', [
                'http_status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
            ]);

            return $this->redirectOtpVerifyFailed();
        }

        $player = $this->findPlayerByPhoneAndCountryCode($sessionCc, $sessionPhone);
        if (!$player) {
            $request->session()->forget([
                'player_otp_phone_digits',
                'player_otp_country_code',
                'player_otp_phone_display',
                'player_otp_sent',
            ]);

            return back()->withInput()->withErrors([
                'phone' => 'No player account found for this phone number. Please contact your club.',
            ]);
        }

        if (strtoupper(trim((string) $player->status)) !== 'ACTIVE') {
            return back()->withInput()->withErrors([
                'phone' => 'Your account is not active. Please contact support.',
            ]);
        }

        $request->session()->forget([
            'player_otp_phone_digits',
            'player_otp_country_code',
            'player_otp_phone_display',
            'player_otp_sent',
        ]);
        $request->session()->regenerate();

        Auth::guard('player')->login($player, $request->boolean('remember'));

        return redirect()->intended($this->redirectPath())->with('success', 'Successfully logged in.');
    }

    protected function redirectPath(): string
    {
        return $this->redirectTo;
    }

    private function redirectOtpVerifyFailed(): RedirectResponse
    {
        return redirect()
            ->route('player.login')
            ->withInput()
            ->with('error', 'OTP verification failed')
            ->with('otp_verify_alert', 'OTP verification failed');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('player')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('player.login');
    }

    /**
     * Match players.phone the same way as admin login (+60 national digits).
     */
    private function findPlayerByPhoneAndCountryCode(string $countryCode, string $phoneDigits): ?Player
    {
        $inputDigits = self::digitsOnly($phoneDigits);
        $normalizedInput = self::normalizeMalaysiaPhoneDigits($phoneDigits);
        if ($inputDigits === '' && $normalizedInput === '') {
            return null;
        }

        $base = Player::query()->whereRaw('LOWER(TRIM(COALESCE(status, ?))) = ?', ['', 'active']);

        if ($countryCode !== self::FIXED_COUNTRY_CODE && $countryCode !== '60') {
            $base->where('country_code', $countryCode);
        }

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

        foreach ($query->get() as $player) {
            $stored = trim((string) $player->phone);
            $storedDigits = self::digitsOnly($stored);
            if ($inputDigits !== '' && $storedDigits !== '' && hash_equals($storedDigits, $inputDigits)) {
                return $player;
            }
            $storedNorm = self::normalizeMalaysiaPhoneDigits($stored);
            if ($normalizedInput !== '' && $storedNorm !== '' && hash_equals($storedNorm, $normalizedInput)) {
                return $player;
            }
        }

        return null;
    }

    private static function digitsOnly(string $raw): string
    {
        return preg_replace('/\D/', '', $raw) ?? '';
    }

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

    private function webhookVerifySucceeded(\Illuminate\Http\Client\Response $response): bool
    {
        if (!$response->successful()) {
            return false;
        }

        return $this->verifyOtpStatusFromPayload($response->json()) === 1;
    }

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
}
