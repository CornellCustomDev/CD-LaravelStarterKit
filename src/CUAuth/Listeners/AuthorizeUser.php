<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Listeners;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthorizeUser
{
    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public function handle(CUAuthenticated $event, ?RemoteIdentity $remoteIdentity = null): void
    {
        $remoteIdentity ??= $this->identityManager->getIdentity();

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
