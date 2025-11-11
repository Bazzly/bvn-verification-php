# BVN Verification PHP - User Documentation

[![PHP Version](https://img.shields.io/badge/php-7.4%2B-blue.svg)](https://packagist.org/packages/bvn-verification/bvn-verification-php)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/bvn-verification/bvn-verification-php.svg)](https://packagist.org/packages/bvn-verification/bvn-verification-php)

A professional PHP package for Nigerian Bank Verification Number (BVN) verification through NIBSS NPS API. Perfect for Laravel, Symfony, and any PHP project requiring secure identity verification.

## 🚀 Quick Start

### Installation

```bash
composer require bvn-verification/bvn-verification-php
```

### Basic Usage

```php
<?php

require 'vendor/autoload.php';

use BVNVerification\BVNVerifier;

// Initialize verifier (mock mode for testing)
$verifier = new BVNVerifier('your-api-key', true, 'json-mock');

// Verify BVN
$result = $verifier->verify('12345678901', 'JOHN DOE');

if ($result->isMatch()) {
    echo "✅ BVN verified successfully!";
    echo "Registered Name: " . $result->getVerifiedName();
} else {
    echo "❌ Verification failed: " . $result->message;
}
```

## 📦 Installation

### For Laravel Projects

1. **Install the package:**
```bash
composer require bvn-verification/bvn-verification-php
```

2. **Publish configuration (optional):**
```bash
php artisan vendor:publish --provider="BVNVerification\\Laravel\\BVNVerificationServiceProvider"
```

3. **Configure environment variables in `.env`:**
```env
BVN_VERIFICATION_API_KEY=your-api-key-here
BVN_VERIFICATION_SANDBOX_MODE=true
BVN_VERIFICATION_MODE=json-mock
```

### For Standalone PHP Projects

```bash
composer require bvn-verification/bvn-verification-php
```

## ⚙️ Configuration

### Laravel Configuration

After installation, add to your `.env` file:

```env
# For production with NIBSS API
BVN_VERIFICATION_API_KEY=your-real-nibss-key
BVN_VERIFICATION_SANDBOX_MODE=false
BVN_VERIFICATION_MODE=live

# For development/testing
BVN_VERIFICATION_API_KEY=mock-key
BVN_VERIFICATION_SANDBOX_MODE=true
BVN_VERIFICATION_MODE=json-mock
```

### Manual Configuration

```php
use BVNVerification\BVNVerifier;

// Production mode (real NIBSS API)
$verifier = new BVNVerifier(
    'your-nibss-api-key',  // API key from NIBSS
    false,                 // sandbox mode (false for production)
    'live'                 // mode: 'live' for real API
);

// Development mode (mock data)
$verifier = new BVNVerifier(
    'mock-key',            // any string for mock mode
    true,                  // sandbox mode (true for testing)
    'json-mock'            // mode: 'json-mock' for mock data
);
```

## 🎯 Usage Examples

### Laravel Usage

**Using Facade:**
```php
<?php

namespace App\Http\Controllers;

use BVNVerification\Laravel\Facades\BVNVerifier;

class VerificationController extends Controller
{
    public function verifyCustomer(Request $request)
    {
        $result = BVNVerifier::verify($request->bvn, $request->full_name);
        
        return response()->json([
            'verified' => $result->isMatch(),
            'registered_name' => $result->getVerifiedName(),
            'message' => $result->message
        ]);
    }
}
```

**Using Dependency Injection:**
```php
<?php

namespace App\Http\Controllers;

use BVNVerification\BVNVerifier;

class VerificationController extends Controller
{
    public function verifyCustomer(Request $request, BVNVerifier $verifier)
    {
        $result = $verifier->verify($request->bvn, $request->full_name);
        
        if ($result->isMatch()) {
            // Proceed with verified user
            return redirect('/dashboard')->with('success', 'BVN verified!');
        }
        
        return back()->with('error', 'BVN verification failed: ' . $result->message);
    }
}
```

### Standalone PHP Usage

**Basic Verification:**
```php
<?php

require 'vendor/autoload.php';

use BVNVerification\BVNVerifier;

$verifier = new BVNVerifier('mock-key', true, 'json-mock');

$customers = [
    ['bvn' => '12345678901', 'name' => 'JOHN DOE'],
    ['bvn' => '98765432109', 'name' => 'JANE SMITH'],
    ['bvn' => '12345678901', 'name' => 'WRONG NAME'] // This will fail
];

foreach ($customers as $customer) {
    $result = $verifier->verify($customer['bvn'], $customer['name']);
    
    echo "BVN: {$customer['bvn']}\n";
    echo "Name: {$customer['name']}\n";
    echo "Status: " . ($result->isMatch() ? 'VERIFIED' : 'FAILED') . "\n";
    
    if ($result->getVerifiedName()) {
        echo "Registered Name: {$result->getVerifiedName()}\n";
    }
    
    echo "Message: {$result->message}\n";
    echo "------------------------\n";
}
```

**Batch Processing:**
```php
<?php

require 'vendor/autoload.php';

use BVNVerification\BVNVerifier;

class BVNService
{
    private $verifier;
    
    public function __construct()
    {
        $this->verifier = new BVNVerifier('mock-key', true, 'json-mock');
    }
    
    public function processBatch(array $applications): array
    {
        $results = [];
        
        foreach ($applications as $app) {
            try {
                $result = $this->verifier->verify($app['bvn'], $app['full_name']);
                
                $results[] = [
                    'application_id' => $app['id'],
                    'bvn' => $app['bvn'],
                    'verified' => $result->isMatch(),
                    'registered_name' => $result->getVerifiedName(),
                    'status' => $result->status,
                    'message' => $result->message,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } catch (Exception $e) {
                $results[] = [
                    'application_id' => $app['id'],
                    'bvn' => $app['bvn'],
                    'verified' => false,
                    'error' => $e->getMessage(),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        return $results;
    }
}

// Usage
$service = new BVNService();
$applications = [
    ['id' => 1, 'bvn' => '12345678901', 'full_name' => 'JOHN DOE'],
    ['id' => 2, 'bvn' => '98765432109', 'full_name' => 'JANE SMITH'],
];

$results = $service->processBatch($applications);
print_r($results);
```

## 🔧 API Reference

### Core Methods

#### `verify(string $bvn, string $customerName): VerificationResponse`

Verifies a BVN against the provided name.

```php
$result = $verifier->verify('12345678901', 'JOHN DOE');

if ($result->isMatch()) {
    // Verification successful
    $registeredName = $result->getVerifiedName();
} else {
    // Verification failed
    $errorMessage = $result->message;
}
```

#### `verifyWithDetails(string $bvn, string $customerName): VerificationResponse`

Alias of `verify()` - provides the same functionality.

### Response Object

The `VerificationResponse` object contains:

- `isMatch(): bool` - Whether BVN and name match
- `getVerifiedName(): ?string` - Registered name from BVN records
- `status: string` - Verification status ('completed', 'failed')
- `message: ?string` - Additional information or error message

### Utility Methods

#### `getBVNRecords(): array` (Mock mode only)

Returns all available BVN records from mock data.

```php
if ($verifier->supportsBVNRecords()) {
    $records = $verifier->getBVNRecords();
    foreach ($records as $record) {
        echo "BVN: {$record['bvn']} - Name: {$record['registered_name']}\n";
    }
}
```

#### `supportsBVNRecords(): bool`

Checks if BVN records are available (only in JSON mock mode).

#### `setMode(string $mode): void`

Switch between operation modes.

```php
$verifier->setMode('live'); // Switch to live NIBSS API
$verifier->setMode('json-mock'); // Switch back to mock mode
```

#### `setSandboxMode(bool $enabled): void`

Toggle sandbox mode.

```php
$verifier->setSandboxMode(false); // Disable sandbox (production)
```

## 🎪 Mock Data

### Using Built-in Mock Data

The package includes sample BVN data for testing:

```php
$verifier = new BVNVerifier('any-key', true, 'json-mock');

// Test with sample data
$testCases = [
    ['12345678901', 'JOHN DOE'],     // ✅ Match
    ['98765432109', 'JANE SMITH'],   // ✅ Match  
    ['12345678901', 'WRONG NAME'],   // ❌ Mismatch
    ['00000000000', 'ANY NAME'],     // ❌ BVN not found
];

foreach ($testCases as $test) {
    $result = $verifier->verify($test[0], $test[1]);
    // Process results...
}
```

### Custom Mock Data

Create your own JSON file:

```json
{
  "bvn_records": [
    {
      "bvn": "11122233344",
      "registered_name": "CHUKWUMA OKORO",
      "first_name": "Chukwuma",
      "last_name": "Okoro",
      "phone": "08011122233",
      "email": "c.okoro@email.com"
    },
    {
      "bvn": "55566677788", 
      "registered_name": "MUSA ABDULLAHI",
      "first_name": "Musa",
      "last_name": "Abdullahi",
      "phone": "08055566677",
      "email": "musa.abdullahi@email.com"
    }
  ]
}
```

## 🔐 Production Setup

### 1. Get NIBSS Credentials

Apply for NIBSS NPS API access at [NIBSS Plc](https://nibss-plc.com.ng/).

### 2. Configure for Production

**Laravel (.env):**
```env
BVN_VERIFICATION_API_KEY=your-production-api-key
BVN_VERIFICATION_SANDBOX_MODE=false
BVN_VERIFICATION_MODE=live
```

**Standalone PHP:**
```php
$verifier = new BVNVerifier(
    'your-production-api-key',
    false, // sandbox mode OFF
    'live' // live mode
);
```

### 3. Error Handling in Production

```php
try {
    $result = $verifier->verify($bvn, $customerName);
    
    if ($result->isMatch()) {
        // Success - proceed with verified user
        $this->createUserAccount($customerData);
    } else {
        // Verification failed
        throw new Exception('BVN verification failed: ' . $result->message);
    }
} catch (\BVNVerification\Exceptions\VerificationException $e) {
    // Handle verification-specific errors
    Log::error('BVN Verification Error: ' . $e->getMessage());
    return response()->json(['error' => 'Verification service unavailable'], 503);
} catch (\Exception $e) {
    // Handle other errors
    Log::error('BVN Service Error: ' . $e->getMessage());
    return response()->json(['error' => 'Service temporarily unavailable'], 500);
}
```

## 🚨 Error Handling

### Common Scenarios

**Invalid BVN Format:**
```php
try {
    $result = $verifier->verify('123', 'John Doe'); // Too short
} catch (\BVNVerification\Exceptions\VerificationException $e) {
    echo "Error: " . $e->getMessage(); // "BVN must be exactly 11 digits"
}
```

**API Connection Issues:**
```php
try {
    $result = $verifier->verify('12345678901', 'John Doe');
} catch (\BVNVerification\Exceptions\AuthenticationException $e) {
    // Invalid API key or authentication failed
    Log::error('Authentication failed: ' . $e->getMessage());
} catch (\BVNVerification\Exceptions\VerificationException $e) {
    // Other verification errors
    Log::error('Verification error: ' . $e->getMessage());
}
```

### Best Practices

```php
public function verifyCustomerBVN($bvn, $customerName)
{
    try {
        // Validate input first
        if (empty($bvn) || empty($customerName)) {
            throw new InvalidArgumentException('BVN and customer name are required');
        }
        
        // Perform verification
        $result = $this->verifier->verify($bvn, $customerName);
        
        // Log the attempt
        Log::info('BVN verification attempted', [
            'bvn' => $bvn,
            'provided_name' => $customerName,
            'verified' => $result->isMatch(),
            'registered_name' => $result->getVerifiedName()
        ]);
        
        return $result;
        
    } catch (\BVNVerification\Exceptions\VerificationException $e) {
        Log::error('BVN verification service error', [
            'bvn' => $bvn,
            'error' => $e->getMessage()
        ]);
        throw new ServiceUnavailableException('Verification service temporarily unavailable');
    } catch (Exception $e) {
        Log::error('Unexpected BVN verification error', [
            'bvn' => $bvn,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
```

## 🧪 Testing

### Laravel Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use BVNVerification\Laravel\Facades\BVNVerifier;

class BVNVerificationTest extends TestCase
{
    public function test_successful_bvn_verification()
    {
        $result = BVNVerifier::verify('12345678901', 'JOHN DOE');
        
        $this->assertTrue($result->isMatch());
        $this->assertEquals('JOHN DOE', $result->getVerifiedName());
    }
    
    public function test_failed_bvn_verification()
    {
        $result = BVNVerifier::verify('12345678901', 'WRONG NAME');
        
        $this->assertFalse($result->isMatch());
        $this->assertStringContainsString('does not match', $result->message);
    }
    
    public function test_invalid_bvn_format()
    {
        $this->expectException(\BVNVerification\Exceptions\VerificationException::class);
        
        BVNVerifier::verify('123', 'John Doe');
    }
}
```

### Standalone PHP Tests

```php
<?php

require 'vendor/autoload.php';

use BVNVerification\BVNVerifier;

class BVNVerificationTest
{
    private $verifier;
    
    public function setUp()
    {
        $this->verifier = new BVNVerifier('test-key', true, 'json-mock');
    }
    
    public function testVerification()
    {
        $result = $this->verifier->verify('12345678901', 'JOHN DOE');
        
        assert($result->isMatch() === true, 'Should verify successfully');
        assert($result->getVerifiedName() === 'JOHN DOE', 'Should return registered name');
        
        echo "✅ All tests passed!\n";
    }
}

$test = new BVNVerificationTest();
$test->setUp();
$test->testVerification();
```

## 📋 Common Use Cases

### E-commerce Registration
```php
public function registerCustomer(Request $request)
{
    $validated = $request->validate([
        'bvn' => 'required|digits:11',
        'full_name' => 'required|string|min:2',
        'email' => 'required|email',
        'phone' => 'required|string'
    ]);
    
    // Verify BVN
    $verificationResult = BVNVerifier::verify(
        $validated['bvn'], 
        $validated['full_name']
    );
    
    if (!$verificationResult->isMatch()) {
        return back()->withErrors([
            'bvn' => 'BVN verification failed. Please ensure your BVN and name match your bank records.'
        ]);
    }
    
    // Create customer account
    $customer = Customer::create([
        'bvn' => $validated['bvn'],
        'verified_name' => $verificationResult->getVerifiedName(),
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'bvn_verified_at' => now()
    ]);
    
    return redirect('/dashboard')->with('success', 'Account created successfully!');
}
```

### Loan Application
```php
public function submitLoanApplication(Request $request)
{
    $verificationResult = BVNVerifier::verify(
        $request->bvn,
        $request->full_name
    );
    
    if ($verificationResult->isMatch()) {
        $application = LoanApplication::create([
            'user_id' => auth()->id(),
            'bvn' => $request->bvn,
            'verified_name' => $verificationResult->getVerifiedName(),
            'amount' => $request->amount,
            'status' => 'under_review'
        ]);
        
        return response()->json([
            'success' => true,
            'application_id' => $application->id,
            'message' => 'Loan application submitted for review'
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'BVN verification failed. Please check your details.'
    ], 422);
}
```

## 🔗 Support

- **Documentation:** [GitHub Repository](https://github.com/bvn-verification/bvn-verification-php)
- **Issues:** [GitHub Issues](https://github.com/bvn-verification/bvn-verification-php/issues)
- **Email:** support@bvn-verification.ng

## 📄 License

This package is open-source software licensed under the [MIT license](LICENSE).

---

**Ready to verify identities securely?** 🚀

Get started today and integrate BVN verification into your PHP applications in minutes!