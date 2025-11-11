<?php

namespace BVNVerification\Clients;

use BVNVerification\DTOs\VerificationRequest;
use BVNVerification\DTOs\VerificationResponse;
use BVNVerification\Exceptions\AuthenticationException;
use BVNVerification\Exceptions\VerificationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NPSClient
{
    private Client $httpClient;
    private string $baseUrl;
    private string $apiKey;
    private bool $sandboxMode;

    public function __construct(string $apiKey, bool $sandboxMode = false)
    {
        $this->apiKey = $apiKey;
        $this->sandboxMode = $sandboxMode;
        $this->baseUrl = $sandboxMode 
            ? 'https://sandbox-api.nibss.gov.ng/nps/'
            : 'https://api.nibss.gov.ng/nps/';
            
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30.0,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);
    }

    public function verifyBVN(VerificationRequest $request): VerificationResponse
    {
        try {
            $response = $this->httpClient->post('bvn/verify', [
                'json' => $request->toArray()
            ]);

            $data = json_decode($response->getBody(), true);

            return new VerificationResponse(
                $data['is_match'] ?? false,
                $data['verified_name'] ?? null,
                $data['status'] ?? 'completed',
                $data['message'] ?? null
            );

        } catch (RequestException $e) {
            // Instead of calling handleException which throws, handle it here
            // and return an error VerificationResponse
            return $this->handleExceptionAsResponse($e);
        }
    }

    private function handleExceptionAsResponse(RequestException $e): VerificationResponse
    {
        $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
        $errorMessage = 'Verification service error';
        
        switch ($statusCode) {
            case 401:
            case 403:
                $errorMessage = 'Invalid API key or unauthorized access';
                break;
            case 400:
                $errorMessage = 'Invalid request parameters';
                break;
            case 422:
                $errorMessage = 'Validation failed';
                break;
            case 429:
                $errorMessage = 'Rate limit exceeded';
                break;
            case 500:
                $errorMessage = 'NPS service unavailable';
                break;
            default:
                $errorMessage = 'Verification service error: ' . $e->getMessage();
                break;
        }

        return new VerificationResponse(
            false,
            null,
            'failed',
            $errorMessage
        );
    }

    /**
     * Original exception throwing method (keep for other uses if needed)
     */
    private function handleException(RequestException $e): void
    {
        $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
        
        switch ($statusCode) {
            case 401:
            case 403:
                throw new AuthenticationException('Invalid API key or unauthorized access', $statusCode);
            case 400:
                throw new VerificationException('Invalid request parameters', $statusCode);
            case 422:
                throw new VerificationException('Validation failed', $statusCode);
            case 429:
                throw new VerificationException('Rate limit exceeded', $statusCode);
            case 500:
                throw new VerificationException('NPS service unavailable', $statusCode);
            default:
                throw new VerificationException('Verification service error: ' . $e->getMessage(), $statusCode);
        }
    }

    public function setSandboxMode(bool $enabled): void
    {
        $this->sandboxMode = $enabled;
        $this->baseUrl = $enabled 
            ? 'https://sandbox-api.nibss.gov.ng/nps/'
            : 'https://api.nibss.gov.ng/nps/';
    }

    /**
     * Get BVN records - not available in live mode
     * This method exists to satisfy the union type requirement
     */
    public function getBVNRecords(): array
    {
        throw new VerificationException('BVN records are not available in live NPS mode');
    }
}