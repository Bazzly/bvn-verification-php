<?php

namespace BVNVerification;

use BVNVerification\Clients\NPSClient;
use BVNVerification\Clients\JSONMockClient;
use BVNVerification\Contracts\VerificationInterface;
use BVNVerification\DTOs\VerificationRequest;
use BVNVerification\DTOs\VerificationResponse;
use BVNVerification\Exceptions\VerificationException;

class BVNVerifier implements VerificationInterface
{
    private NPSClient|JSONMockClient $client;
    private string $apiKey;
    private bool $sandboxMode;
    private string $mode; // 'live', 'json-mock'

    public function __construct(string $apiKey = 'mock-key', bool $sandboxMode = false, string $mode = 'json-mock')
    {
        $this->apiKey = $apiKey;
        $this->sandboxMode = $sandboxMode;
        $this->mode = $mode;
        
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        switch ($this->mode) {
            case 'live':
                $this->client = new NPSClient($this->apiKey, $this->sandboxMode);
                break;
            case 'json-mock':
            default:
                $this->client = new JSONMockClient($this->sandboxMode);
                break;
        }
    }

    public function verify(string $accountNumber, string $customerName): VerificationResponse
    {
        $this->validateInput($accountNumber, $customerName);
        
        $request = new VerificationRequest($accountNumber, $customerName);
        return $this->client->verifyBVN($request);
    }

    public function verifyWithDetails(string $accountNumber, string $customerName): VerificationResponse
    {
        return $this->verify($accountNumber, $customerName);
    }

    public function setSandboxMode(bool $enabled): void
    {
        $this->sandboxMode = $enabled;
        $this->client->setSandboxMode($enabled);
    }

    public function setMode(string $mode): void
    {
        $allowedModes = ['live', 'json-mock'];
        if (!in_array($mode, $allowedModes)) {
            throw new VerificationException('Invalid mode. Allowed: ' . implode(', ', $allowedModes));
        }

        if ($mode !== $this->mode) {
            $this->mode = $mode;
            $this->initializeClient();
        }
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function isSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get BVN records (only available in JSON mock mode)
     */
    public function getBVNRecords(): array
    {
        if ($this->mode === 'json-mock') {
            return $this->client->getBVNRecords();
        }
        
        throw new VerificationException('BVN records are only available in JSON mock mode');
    }

    /**
     * Check if BVN records are available
     */
    public function supportsBVNRecords(): bool
    {
        return $this->mode === 'json-mock';
    }

    private function validateInput(string $accountNumber, string $customerName): void
    {
        if (empty($accountNumber) || empty($customerName)) {
            throw new VerificationException('Account number and customer name are required');
        }

        if (!preg_match('/^\d{11}$/', $accountNumber)) {
            throw new VerificationException('BVN must be exactly 11 digits');
        }

        if (strlen($customerName) < 2) {
            throw new VerificationException('Customer name must be at least 2 characters long');
        }
    }
}