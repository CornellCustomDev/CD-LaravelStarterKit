<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Listeners;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthorizeUser
{
    public function handle(CUAuthenticated $event): void
    {
        // Look for a matching user.
        $userModel = config('auth.providers.users.model');
        $userLookupField = $event->userLookupField;
        $user = $userModel::firstWhere($userLookupField, $event->userId);
        $shib = ShibIdentity::fromServerVars();

        if (empty($user)) {
            // User does not exist, so create them.
            $user = new $userModel;
            $user->$userLookupField = $event->userId;
            $user->name = $shib->displayName;
            $user->email = $shib->mail;
            $user->password = Str::random(32);
            $user->save();
            Log::info("AuthorizeUser: Created user $event->userId with ID $user->id.");
        }

        auth()->login($user);
        Log::info("AuthorizeUser: Logged in user $event->userId.");
    }
}
