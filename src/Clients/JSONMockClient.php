<?php

namespace BVNVerification\Clients;

use BVNVerification\Contracts\ClientInterface;
use BVNVerification\DTOs\VerificationRequest;
use BVNVerification\DTOs\VerificationResponse;
use BVNVerification\Exceptions\VerificationException;

class JSONMockClient implements ClientInterface
{
    private bool $sandboxMode;
    private array $bvnRecords;
    private string $dataFile;

    public function __construct(bool $sandboxMode = false, string $dataFile = null)
    {
        $this->sandboxMode = $sandboxMode;
        $this->dataFile = $dataFile ?? __DIR__ . '/../../mock_data/bvn_records.json';
        $this->loadBVNRecords();
    }

    private function loadBVNRecords(): void
    {
        if (!file_exists($this->dataFile)) {
            throw new VerificationException("Mock data file not found: " . $this->dataFile);
        }

        $jsonData = file_get_contents($this->dataFile);
        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new VerificationException("Invalid JSON in mock data file: " . json_last_error_msg());
        }

        $this->bvnRecords = $data['bvn_records'] ?? [];
    }

    public function verifyBVN(VerificationRequest $request): VerificationResponse
    {
        // Simulate API delay
        usleep(300000); // 0.3 seconds

        // Validate BVN format
        if (!preg_match('/^\d{11}$/', $request->accountNumber)) {
            return new VerificationResponse(false, null, 'failed', 'BVN must be exactly 11 digits');
        }

        // Find BVN in records
        foreach ($this->bvnRecords as $record) {
            if ($record['bvn'] === $request->accountNumber) {
                $registeredName = $record['registered_name'];
                $inputName = strtoupper(trim($request->customerName));
                
                // Simple name matching
                $isMatch = $this->namesMatch($inputName, $registeredName);
                
                if ($isMatch) {
                    return new VerificationResponse(
                        true, 
                        $registeredName, 
                        'completed', 
                        'BVN verification successful'
                    );
                } else {
                    return new VerificationResponse(
                        false, 
                        $registeredName, 
                        'completed', 
                        "Name does not match BVN records. Registered name: $registeredName"
                    );
                }
            }
        }

        // BVN not found
        return new VerificationResponse(false, null, 'failed', 'BVN not found in registry');
    }

    private function namesMatch(string $inputName, string $registeredName): bool
    {
        $inputName = preg_replace('/\s+/', ' ', trim($inputName));
        $registeredName = preg_replace('/\s+/', ' ', trim($registeredName));
        
        return $inputName === $registeredName;
    }

    public function setSandboxMode(bool $enabled): void
    {
        $this->sandboxMode = $enabled;
    }

    public function getBVNRecords(): array
    {
        return $this->bvnRecords;
    }

    /**
     * Add a new BVN record to the mock data
     */
    public function addBVNRecord(array $record): bool
    {
        $required = ['bvn', 'registered_name'];
        foreach ($required as $field) {
            if (!isset($record[$field])) {
                return false;
            }
        }

        foreach ($this->bvnRecords as $existing) {
            if ($existing['bvn'] === $record['bvn']) {
                return false;
            }
        }

        $this->bvnRecords[] = $record;
        return $this->saveBVNRecords();
    }

    private function saveBVNRecords(): bool
    {
        $data = ['bvn_records' => $this->bvnRecords];
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($this->dataFile, $json) !== false;
    }
}