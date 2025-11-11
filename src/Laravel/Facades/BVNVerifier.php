<?php
// src/Laravel/Facades/BVNVerifier.php

namespace BVNVerification\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class BVNVerifier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bvn-verifier';
    }
}