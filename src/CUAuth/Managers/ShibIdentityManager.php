<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Managers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShibIdentityManager implements IdentityManager
{
    // Shibboleth fields generally available from either cit or weill IdPs.
    public const SHIB_FIELDS = [
        'Shib_Application_ID', // <vhost|applicationId>
        'Shib_Authentication_Instant', // YYYY-MM-DDT00:00:00.000Z
        'Shib_Identity_Provider', // https://shibidp.cit.cornell.edu/idp/shibboleth|https://login.weill.cornell.edu/idp
        'Shib_Session_Expires', // timestamp
        'Shib_Session_Inactivity', // timestamp
        'displayName', // John Doe
        'eduPersonAffiliations', // employee;member;staff
        'eduPersonPrincipalName', // netid@cornell.edu|cwid@med.cornell.edu
        'eduPersonScopedAffiliation', // employee@[med.]cornell.edu;member@[med.]cornell.edu;staff@cornell.edu
        'givenName', // John
        'mail', // alias email
        'sn', // Doe
        'uid', // netid|cwid
    ];

    /**
     * Shibboleth server variables will be retrieved from the request if not provided.
     */
    public static function fromServerVars(?array $serverVars = null): RemoteIdentity
    {
        if (empty($serverVars)) {
            $serverVars = app('request')->server();
        }

        return new RemoteIdentity(
            idp: $serverVars['Shib_Identity_Provider'] ?? '',
            uid: $serverVars['uid'] ?? '',
            displayName: $serverVars['displayName']
                ?? $serverVars['cn']
                ?? trim(($serverVars['givenName'] ?? '').' '.($serverVars['sn'] ?? '')),
            email: $serverVars['eduPersonPrincipalName']
              ?? $serverVars['mail'] ?? '',
            data: $serverVars,
        );
    }

    public function storeIdentity(?RemoteIdentity $remoteIdentity = null): ?RemoteIdentity
    {
        $remoteIdentity ??= self::fromServerVars();

        session()->put('remoteIdentity', $remoteIdentity);

        return $remoteIdentity;
    }

    public function hasIdentity(?Request $request = null): bool
    {
        $remoteUser = self::getRemoteUser($request);

        return ! empty($remoteUser);
    }

    public function getIdentity(): ?RemoteIdentity
    {
        /** @var RemoteIdentity|null $remoteIdentity */
        $remoteIdentity = session()->get('remoteIdentity');

        return $remoteIdentity;
    }

    public function getSsoUrl(string $redirectUrl): string
    {
        $url = config('cu-auth.shibboleth_login_url');
        $query = Arr::query([
            'target' => $redirectUrl,
        ]);

        return $url.'?'.$query;
    }

    public function getSloUrl(string $returnUrl): string
    {
        if (self::getRemoteUserOverride()) {
            return $returnUrl;
        }

        $url = config('cu-auth.shibboleth_logout_url');
        $query = Arr::query([
            'return' => $returnUrl,
        ]);

        return $url.'?'.$query;
    }

    public function getMetadata(): ?string
    {
        return null;
    }

    public static function getRemoteUser(?Request $request = null): ?string
    {
        if (empty($request)) {
            $request = app('request');
        }

        // If this is a local development environment, allow the local override.
        $remote_user_override = self::getRemoteUserOverride();

        // Apache mod_shib populates the remote user variable if someone is logged in.
        return $request->server(config('cu-auth.apache_shib_user_variable')) ?: $remote_user_override;
    }

    public static function getRemoteUserOverride(): ?string
    {
        // If this is a local development environment, allow the local override.
        return app()->isLocal() ? config('cu-auth.remote_user_override') : null;
    }
}
