<?php

namespace BVNVerification\Exceptions;

class AuthenticationException extends VerificationException
{
    protected $code = 401;
    protected $message = 'Authentication failed. Please check your API credentials.';
}