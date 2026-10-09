<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\TicketingIdentityException;
use App\Http\Controllers\Controller;
use App\Services\TicketingIdentityService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly TicketingIdentityService $identity)
    {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $payload = $this->identity->login($credentials['email'], $credentials['password']);
        } catch (TicketingIdentityException $e) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $e->getMessage()]);
        }

        $user = $this->identity->syncLocalUser($payload['user']);

        $request->session()->regenerate();
        $request->session()->put('ticketing_token', $payload['access_token']);
        Auth::login($user, $request->boolean('remember'));

        Log::info('User authenticated via ticketing identity service', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->identity->logout($request->session()->get('ticketing_token'));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
