<?php

namespace BVNVerification\Contracts;

interface VerificationInterface
{
    public function verify(string $accountNumber, string $customerName);
    
    public function verifyWithDetails(string $accountNumber, string $customerName);
    
    public function setSandboxMode(bool $enabled);
}