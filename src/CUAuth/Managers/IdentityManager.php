<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Managers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use Illuminate\Http\Request;

interface IdentityManager
{
    public function hasIdentity(?Request $request = null): bool;

    public function storeIdentity(?RemoteIdentity $remoteIdentity = null): ?RemoteIdentity;

    public function getIdentity(): ?RemoteIdentity;

    public function getSsoUrl(string $redirectUrl): string;

    public function getSloUrl(string $returnUrl): string;
}
