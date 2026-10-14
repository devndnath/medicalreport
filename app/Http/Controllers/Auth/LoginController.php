<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'This account has been deactivated. Contact an administrator.']);
        }

        $request->session()->regenerate();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'logged_in',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'logged_out',
            'ip_address' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
