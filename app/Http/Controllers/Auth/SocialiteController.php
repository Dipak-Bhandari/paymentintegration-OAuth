<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Create a new class instance.
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            return redirect()->route('login')->with('error', 'Unsupported provider: ' . ucfirst($provider));
        }
        try {
        return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors('provider', 'Failed to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }
    }

    public function callback(string $provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            return redirect()->route('login')->withErrors('provider', 'Unsupported provider: ' . ucfirst($provider));
        }

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors('provider', 'Failed to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }

        $user = User::updateOrCreate(
            [
                'provider_id' => $socialiteUser->getId(),
                'provider_name' => $provider
            ],
            [
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'email_verified_at' => now(),
                'provider_token' => $socialiteUser->token,
                'provider_refresh_token' => $socialiteUser->refreshToken,
            ]
        );
        // Log the user in
        Auth::login($user);
        return redirect('/dashboard');
    }
}
