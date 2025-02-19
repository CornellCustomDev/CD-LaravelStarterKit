<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Listeners;

use CornellCustomDev\LaravelStarterKit\CUAuth\CUAuthServiceProvider;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\ShibIdentityManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthorizeUser
{
    public function handle(CUAuthenticated $event, ?array $serverVars = null): void
    {
        $remoteIdentity = match (config('cu-auth.identity_manager')) {
            CUAuthServiceProvider::APACHE_SHIB => ShibIdentityManager::fromServerVars($serverVars),
            CUAuthServiceProvider::PHP_SAML => SamlIdentityManager::getIdentity(),
        };

        // Look for a matching user.
        $userModel = config('auth.providers.users.model');
        $user = $userModel::firstWhere('email', $remoteIdentity->email());

        if (empty($user)) {
            // User does not exist, so create them.
            $user = new $userModel;
            $user->name = $remoteIdentity->name();
            $user->email = $remoteIdentity->email();
            $user->password = Str::random(32);
            $user->save();
            Log::info("AuthorizeUser: Created user $user->email with ID $user->id.");
        }

        auth()->login($user);
        Log::info("AuthorizeUser: Logged in user $user->email.");
    }
}
