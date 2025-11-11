<?php

namespace BVNVerification\Exceptions;

class InvalidRequestException extends VerificationException
{
    protected $code = 400;
    protected $message = 'Invalid request parameters provided.';
}