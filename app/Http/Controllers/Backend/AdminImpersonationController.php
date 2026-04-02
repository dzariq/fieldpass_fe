<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminImpersonationController extends Controller
{
    public const SESSION_IMPERSONATOR_KEY = 'admin_impersonator_id';

    public function store(Request $request, Admin $admin): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['association.create']);

        if ($request->session()->has(self::SESSION_IMPERSONATOR_KEY)) {
            return redirect()->route('admin.dashboard')->withErrors([
                'error' => 'Already impersonating. Leave that session first.',
            ]);
        }

        if ((int) $admin->id === (int) auth()->id()) {
            return back()->withErrors(['error' => 'You cannot impersonate yourself.']);
        }

        if ($admin->status !== 'ACTIVE') {
            return back()->withErrors(['error' => 'Cannot impersonate an inactive admin.']);
        }

        $impersonatorId = (int) auth()->id();

        // Store impersonator before login(). SessionGuard::login() already calls session->migrate(true);
        // A second session()->regenerate() here breaks persistence and yields 500 (guest) on next request.
        $request->session()->put(self::SESSION_IMPERSONATOR_KEY, $impersonatorId);
        Auth::guard('admin')->login($admin);

        Log::warning('Admin impersonation started', [
            'impersonator_id' => $impersonatorId,
            'impersonated_id' => $admin->id,
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'You are signed in as '.$admin->name.'.');
    }

    public function leave(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get(self::SESSION_IMPERSONATOR_KEY);
        if (!$impersonatorId) {
            return redirect()->route('admin.dashboard')->withErrors([
                'error' => 'You are not impersonating another admin.',
            ]);
        }

        $original = Admin::query()
            ->where('id', $impersonatorId)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$original) {
            $request->session()->forget(self::SESSION_IMPERSONATOR_KEY);
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['error' => 'Your original admin account is missing or inactive. Please sign in again.']);
        }

        $impersonatedId = (int) auth()->id();

        Auth::guard('admin')->login($original);
        $request->session()->forget(self::SESSION_IMPERSONATOR_KEY);

        Log::warning('Admin impersonation ended', [
            'restored_admin_id' => $original->id,
            'had_impersonated_id' => $impersonatedId,
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'You are back in your own account ('.$original->name.').');
    }
}
