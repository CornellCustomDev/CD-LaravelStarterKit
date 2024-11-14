<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CUAuthenticated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $remoteUser,
    ) {}
}
