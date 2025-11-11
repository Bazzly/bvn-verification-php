<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BVNVerification\BVNVerifier;

class VerificationService
{
    private BVNVerifier $verifier;
    
    public function __construct(string $apiKey, bool $isProduction = false)
    {
        $this->verifier = new BVNVerifier($apiKey, !$isProduction);
    }
    
    public function verifyCustomer(array $customerData): array
    {
        try {
            $result = $this->verifier->verify(
                $customerData['bvn'],
                $customerData['full_name']
            );
            
            return [
                'success' => true,
                'verified' => $result->isMatch(),
                'verified_name' => $result->getVerifiedName(),
                'status' => $result->status
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'verified' => false
            ];
        }
    }
}

// Usage
$service = new VerificationService('your-api-key', false); // false = use sandbox

$customer = [
    'bvn' => '12345678901',
    'full_name' => 'John Doe'
];

$verificationResult = $service->verifyCustomer($customer);
print_r($verificationResult);