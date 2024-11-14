<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Listeners;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthorizeUser
{
    public function handle(CUAuthenticated $event, ?array $serverVars = null): void
    {
        $shibboleth = ShibIdentity::fromServerVars($serverVars);

        // Look for a matching user.
        $userModel = config('auth.providers.users.model');
        $user = $userModel::firstWhere('email', $shibboleth->email());

        if (empty($user)) {
            // User does not exist, so create them.
            $user = new $userModel;
            $user->name = $shibboleth->name();
            $user->email = $shibboleth->email();
            $user->password = Str::random(32);
            $user->save();
            Log::info("AuthorizeUser: Created user $user->email with ID $user->id.");
        }

        auth()->login($user);
        Log::info("AuthorizeUser: Logged in user $user->email.");
    }
}
