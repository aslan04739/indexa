<?php

namespace App\Services;

class LinkCheckResult
{
    public const OK = 'ok';

    public const MISSING = 'missing';          // page loads, no link to the target

    public const NOINDEX = 'noindex';          // link present but the page is noindex

    public const UNREACHABLE = 'unreachable';  // page does not load (network error or non-2xx)

    public function __construct(
        public readonly string $status,
        public readonly ?string $rel = null,
        public readonly ?string $detail = null,
    ) {}

    public function ok(): bool
    {
        return $this->status === self::OK;
    }
}
