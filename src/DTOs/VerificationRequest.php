<?php

namespace BVNVerification\DTOs;

class VerificationRequest
{
    public string $accountNumber;
    public string $customerName;
    
    public function __construct(string $accountNumber, string $customerName)
    {
        $this->accountNumber = $accountNumber;
        $this->customerName = $customerName;
    }
    
    public function toArray(): array
    {
        return [
            'account_number' => $this->accountNumber,
            'customer_name' => $this->customerName
        ];
    }
}