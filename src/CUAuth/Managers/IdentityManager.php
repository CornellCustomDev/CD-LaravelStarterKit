<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Managers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use Illuminate\Http\Request;

interface IdentityManager
{
    public function hasIdentity(): bool;

    public function getIdentity(): ?RemoteIdentity;

    public function storeIdentity(): ?RemoteIdentity;

    public function getSsoUrl(string $redirectUrl): string;

    public function getSsoReturnUrl(Request $request): string;

    public function getSloUrl(string $returnUrl): string;

    public function getMetadata(): ?string;
}
