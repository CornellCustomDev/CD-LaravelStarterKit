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

    public function hasIdentity(): bool
    {
        return ! empty($this->getIdentity());
    }

    public function getIdentity(): ?RemoteIdentity
    {
        /** @var RemoteIdentity|null $remoteIdentity */
        $remoteIdentity = session()->get('remoteIdentity');

        if (empty($remoteIdentity)) {
            $remoteIdentity = $this->storeIdentity();
        }

        return $remoteIdentity;
    }

    public function storeIdentity(): ?RemoteIdentity
    {
        $remoteIdentity = $this->retrieveIdentity();
        session()->put('remoteIdentity', $remoteIdentity);

        return $remoteIdentity;
    }

    public function getSsoUrl(string $redirectUrl): string
    {
        $url = config('cu-auth.shibboleth_login_url');
        $query = Arr::query([
            'target' => route('cu-auth.sso-acs', ['redirect_url' => $redirectUrl]),
        ]);

        return $url.'?'.$query;
    }

    public function getSsoReturnUrl(Request $request): string
    {
        return $request->query('redirect_url', '/');
    }

    public function getSloUrl(string $returnUrl): string
    {
        if ($this->getRemoteUserOverride()) {
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

    public function retrieveIdentity(?array $attributes = null, ?Request $request = null): ?RemoteIdentity
    {
        $attributes ??= Arr::only(app('request')->server(), self::SHIB_FIELDS);
        $remoteUserVal = $this->getRemoteUserVal($request ?: app('request'));
        $uid = $attributes['uid'] ?? $remoteUserVal;

        if (empty($uid)) {
            return null;
        }

        return RemoteIdentity::fromData(
            idp: $attributes['Shib_Identity_Provider'] ?? '',
            uid: $uid,
            data: $attributes ?: ['REMOTE_USER' => $remoteUserVal],
            cn: $attributes['cn'] ?? null,
            givenName: $attributes['givenName'] ?? null,
            sn: $attributes['sn'] ?? null,
            displayName: $attributes['displayName'] ?? $remoteUserVal,
            eduPersonPrincipalName: $attributes['eduPersonPrincipalName'] ?? null,
            mail: $attributes['mail'] ?? ($remoteUserVal.'@cornell.edu'),
        );
    }

    private function getRemoteUserVal(?Request $request = null): ?string
    {
        // If this is a local development environment, allow the local override.
        $remote_user_override = $this->getRemoteUserOverride();

        // Apache mod_shib populates the remote user variable if someone is logged in.
        return $request->server(config('cu-auth.apache_shib_user_variable'))
            ?: $remote_user_override;
    }

    private function getRemoteUserOverride(): ?string
    {
        // If this is a local development environment, allow the local override.
        return app()->isProduction()
            ? null
            : config('cu-auth.remote_user_override');
    }
}
