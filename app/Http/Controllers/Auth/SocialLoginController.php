<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AppleLoginToken;
use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * @param Request $request
     * @param AppleLoginToken $appleToken
     * @return Application|RedirectResponse|Redirector
     */
    public function loginWithApple(Request $request, #[\SensitiveParameter] AppleLoginToken $appleToken): Application|Redirector|RedirectResponse
    {
        logger("Apple login");
        try {

            config()->set('services.apple.client_secret', $appleToken->generate());
            return Socialite::driver('apple')->redirect();

        } catch (Exception $e) {
            logger("Apple login error: ".$e->getMessage());
            return redirect('/')->withErrors('Something went wrong please try again.');
        }
    }

    public function loginWithAppleCallback(Request $request, #[\SensitiveParameter] AppleLoginToken $appleToken) {
        logger("callback request");
        logger("request: ".json_encode($request->all(), JSON_THROW_ON_ERROR));
        logger("appleToken : ".json_encode($appleToken, JSON_THROW_ON_ERROR));
        try {
            config()?->set('services.apple.client_secret', $appleToken->generate());
            $payload = Socialite::driver('apple')->stateless()->user();
            logger("payload: ".$payload);
            logger("payload encode : ".json_encode($payload));
            //$payload variable contain user information like email, name etc.
            // use this variable for create and login user inside your app.
            $user = User::where('email', $payload['email'])->first();
            if ($user) {
                Auth::login($user);
            } else {
                // Create user in you database
            }

        } catch (Exception $e) {
            return redirect('/')->withErrors('Something went wrong please try again.');
        }
    }
}
