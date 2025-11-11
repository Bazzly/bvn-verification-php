<?php

namespace BVNVerification\DTOs;

class VerificationResponse
{
    public bool $isMatch;
    public ?string $verifiedName;
    public string $status;
    public ?string $message;
    
    public function __construct(bool $isMatch, ?string $verifiedName = null, string $status = 'completed', ?string $message = null)
    {
        $this->isMatch = $isMatch;
        $this->verifiedName = $verifiedName;
        $this->status = $status;
        $this->message = $message;
    }
    
    public function isMatch(): bool
    {
        return $this->isMatch;
    }
    
    public function getVerifiedName(): ?string
    {
        return $this->verifiedName;
    }
}