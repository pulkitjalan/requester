<?php

namespace Tymon\JWTAuth\Facades;

use Illuminate\Support\Facades\Facade;

class Requester extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'requester';
    }
}
