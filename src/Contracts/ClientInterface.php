<?php

namespace BVNVerification\Contracts;

use BVNVerification\DTOs\VerificationRequest;
use BVNVerification\DTOs\VerificationResponse;

interface ClientInterface
{
    public function verifyBVN(VerificationRequest $request): VerificationResponse;
    
    public function setSandboxMode(bool $enabled): void;
    
    public function getBVNRecords(): array;
}
