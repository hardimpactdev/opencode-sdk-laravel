<?php

namespace HardImpact\OpenCode\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HardImpact\OpenCode\OpenCode
 */
class OpenCode extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \HardImpact\OpenCode\OpenCode::class;
    }
}
