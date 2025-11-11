# Step-by-Step Guide to Make Your Package Installable

## Step 1: Update composer.json for Package Distribution

Replace your current `composer.json` with this:

```json
{
    "name": "bvn-verification/bvn-verification-php",
    "description": "A PHP package for Nigerian BVN verification through NIBSS NPS - Perfect for Laravel, Symfony, and any PHP project",
    "type": "library",
    "keywords": ["bvn", "verification", "nigeria", "nibss", "laravel", "fintech", "kyc", "identity"],
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "BVNVerification\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "BVNVerification\\Tests\\": "tests/"
        }
    },
    "require": {
        "php": "^7.4|^8.0",
        "guzzlehttp/guzzle": "^7.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0",
        "orchestra/testbench": "^6.0|^7.0"
    },
    "extra": {
        "laravel": {
            "providers": [
                "BVNVerification\\Laravel\\BVNVerificationServiceProvider"
            ],
            "aliases": {
                "BVNVerifier": "BVNVerification\\Laravel\\Facades\\BVNVerifier"
            }
        }
    },
    "authors": [
        {
            "name": "Your Name",
            "email": "your@email.com",
            "homepage": "https://github.com/yourusername"
        }
    ],
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

## Step 2: Create Laravel Service Provider

```bash
mkdir -p src/Laravel
mkdir -p src/Laravel/Facades
```

```php
<?php
// src/Laravel/BVNVerificationServiceProvider.php

namespace BVNVerification\Laravel;

use BVNVerification\BVNVerifier;
use Illuminate\Support\ServiceProvider;

class BVNVerificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/bvn-verification.php', 'bvn-verification'
        );

        $this->app->singleton('bvn-verifier', function ($app) {
            $config = $app['config']['bvn-verification'];
            
            return new BVNVerifier(
                $config['api_key'],
                $config['sandbox_mode'] ?? false,
                $config['mode'] ?? 'json-mock'
            );
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/bvn-verification.php' => config_path('bvn-verification.php'),
            ], 'bvn-verification-config');
        }
    }
}
```

## Step 3: Create Laravel Facade

```php
<?php
// src/Laravel/Facades/BVNVerifier.php

namespace BVNVerification\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class BVNVerifier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bvn-verifier';
    }
}
```

## Step 4: Create Configuration File

```bash
mkdir config
```

```php
<?php
// config/bvn-verification.php

return [
    /*
    |--------------------------------------------------------------------------
    | BVN Verification Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for BVN verification package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your NIBSS NPS API key. For testing, you can use any string in mock mode.
    |
    */
    'api_key' => env('BVN_VERIFICATION_API_KEY', 'mock-key'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | Whether to use sandbox environment. Always true for mock modes.
    |
    */
    'sandbox_mode' => env('BVN_VERIFICATION_SANDBOX_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | Operation mode: 'live' for real NIBSS API, 'json-mock' for JSON-based mock data
    |
    */
    'mode' => env('BVN_VERIFICATION_MODE', 'json-mock'),

    /*
    |--------------------------------------------------------------------------
    | JSON Data File Path (for mock mode)
    |--------------------------------------------------------------------------
    |
    | Path to JSON file containing mock BVN data (only for json-mock mode)
    |
    */
    'json_data_file' => env('BVN_VERIFICATION_JSON_FILE', null),
];
```

## Step 5: Create Comprehensive README.md

```markdown
# BVN Verification PHP

[![PHP Version](https://img.shields.io/badge/php-7.4%2B-blue.svg)](https://packagist.org/packages/bvn-verification/bvn-verification-php)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/bvn-verification/bvn-verification-php.svg)](https://packagist.org/packages/bvn-verification/bvn-verification-php)

A comprehensive PHP package for Nigerian Bank Verification Number (BVN) verification through NIBSS NPS API. Perfect for Laravel, Symfony, and any PHP project requiring identity verification.

## 🚀 Features

- ✅ **BVN + Name Verification** - Verify BVN against registered names
- ✅ **Multiple Modes** - Live NIBSS API or JSON-based mock data
- ✅ **Laravel Integration** - Service provider and facade included
- ✅ **Simple API** - Clean, developer-friendly interface
- ✅ **Error Handling** - Comprehensive exception handling
- ✅ **Mock Data** - Built-in mock data for development and testing
- ✅ **PSR-4 Compliant** - Works with any modern PHP framework

## 📦 Installation

### For Laravel Projects

```bash
composer require bvn-verification/bvn-verification-php
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="BVNVerification\\Laravel\\BVNVerificationServiceProvider"
```

### For Other PHP Projects

```bash
composer require bvn-verification/bvn-verification-php
```

## ⚙️ Configuration

### Laravel Configuration

After publishing, configure your `.env` file:

```env
BVN_VERIFICATION_API_KEY=your-nibss-api-key
BVN_VERIFICATION_SANDBOX_MODE=true
BVN_VERIFICATION_MODE=json-mock
BVN_VERIFICATION_JSON_FILE=null
```

Or edit `config/bvn-verification.php` directly.

### Standalone PHP Configuration

```php
<?php

require 'vendor/autoload.php';

use BVNVerification\BVNVerifier;

// For live NIBSS API
$verifier = new BVNVerifier('your-nibss-api-key', false, 'live');

// For mock mode (development)
$verifier = new BVNVerifier('mock-key', true, 'json-mock');
```

## 🎯 Quick Start

### Laravel Usage

```php
<?php

namespace App\Http\Controllers;

use BVNVerification\Laravel\Facades\BVNVerifier;

class VerificationController extends Controller
{
    public function verifyBVN(Request $request)
    {
        $result = BVNVerifier::verify($request->bvn, $request->name);
        
        if ($result->isMatch()) {
            return response()->json([
                'success' => true,
                'verified' => true,
                'verified_name' => $result->getVerifiedName()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'verified' => false,
            'message' => $result->message
        ]);
    }
}
```

### Standalone PHP Usage

```php
<?php

require 'vendor/autoload.php';

use BVNVerification\BVNVerifier;

$verifier = new BVNVerifier('mock-key', true, 'json-mock');

$result = $verifier->verify('12345678901', 'JOHN DOE');

if ($result->isMatch()) {
    echo "✅ BVN verified successfully!\n";
    echo "📛 Verified Name: " . $result->getVerifiedName() . "\n";
} else {
    echo "❌ BVN verification failed\n";
    echo "💡 Reason: " . $result->message . "\n";
}
```

## 🔧 API Reference

### Basic Verification

```php
$result = $verifier->verify(string $bvn, string $customerName);
```

Returns a `VerificationResponse` object with:
- `isMatch(): bool` - Whether the verification was successful
- `getVerifiedName(): ?string` - The registered name if available
- `status: string` - Status of the verification
- `message: ?string` - Additional message or error description

### Advanced Methods

```php
// Get all available BVN records (mock mode only)
$records = $verifier->getBVNRecords();

// Check if BVN records are available
$available = $verifier->supportsBVNRecords();

// Switch modes dynamically
$verifier->setMode('live'); // or 'json-mock'

// Toggle sandbox mode
$verifier->setSandboxMode(false);
```

## 🎪 Mock Data

### Using JSON Mock Data

Create a JSON file with your test data:

```json
{
  "bvn_records": [
    {
      "bvn": "12345678901",
      "registered_name": "JOHN DOE",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "08012345678"
    },
    {
      "bvn": "98765432109",
      "registered_name": "JANE SMITH",
      "first_name": "Jane",
      "last_name": "Smith", 
      "phone": "08098765432"
    }
  ]
}
```

Use with custom JSON file:

```php
$verifier = new BVNVerifier('mock-key', true, 'json-mock');
// JSON file will be automatically loaded from mock_data/bvn_records.json
```

## 🔐 Production Setup

### 1. Get NIBSS Credentials
Apply for NIBSS NPS API credentials at [NIBSS](https://nibss-plc.com.ng/).

### 2. Update Configuration
```env
BVN_VERIFICATION_API_KEY=your-production-api-key
BVN_VERIFICATION_SANDBOX_MODE=false
BVN_VERIFICATION_MODE=live
```

### 3. Test Thoroughly
```php
// Test with real credentials in sandbox first
$verifier = new BVNVerifier('real-api-key', true, 'live');
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
    public function test_bvn_verification_success()
    {
        $result = BVNVerifier::verify('12345678901', 'JOHN DOE');
        
        $this->assertTrue($result->isMatch());
        $this->assertEquals('JOHN DOE', $result->getVerifiedName());
    }
}
```

### Standalone Tests

```php
<?php

use BVNVerification\BVNVerifier;

$verifier = new BVNVerifier('mock-key', true, 'json-mock');

// Test valid verification
$result = $verifier->verify('12345678901', 'JOHN DOE');
assert($result->isMatch() === true);

// Test invalid verification  
$result = $verifier->verify('12345678901', 'WRONG NAME');
assert($result->isMatch() === false);
```

## 🚨 Error Handling

```php
try {
    $result = $verifier->verify('12345678901', 'JOHN DOE');
    
    if ($result->isMatch()) {
        // Success
    } else {
        // Verification failed
        echo "Failed: " . $result->message;
    }
} catch (\BVNVerification\Exceptions\VerificationException $e) {
    // Handle verification errors
    echo "Error: " . $e->getMessage();
} catch (\Exception $e) {
    // Handle other errors
    echo "Unexpected error: " . $e->getMessage();
}
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This package is open-source software licensed under the [MIT license](LICENSE).

## 🆕 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🐛 Reporting Issues

If you discover any issues, please report them on [GitHub Issues](https://github.com/bvn-verification/bvn-verification-php/issues).

## 🔗 Useful Links

- [NIBSS Official Website](https://nibss-plc.com.ng/)
- [Packagist Page](https://packagist.org/packages/bvn-verification/bvn-verification-php)
- [GitHub Repository](https://github.com/bvn-verification/bvn-verification-php)

---

**Built with ❤️ for Nigerian developers and businesses**
```

## Step 6: Create Additional Files

### CHANGELOG.md
```markdown
# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2024-01-01

### Added
- Initial release
- BVN verification with NIBSS NPS API
- JSON mock mode for development
- Laravel service provider and facade
- Comprehensive documentation
```

### CONTRIBUTING.md
```markdown
# Contributing Guide

We love your input! We want to make contributing as easy and transparent as possible.

## Development Setup

1. Fork the repo
2. Clone your fork
3. Install dependencies: `composer install`
4. Create a branch for your feature

## Code Style

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Add type hints where possible

## Pull Request Process

1. Ensure tests pass
2. Update README.md if needed
3. Update CHANGELOG.md
4. Submit PR with clear description
```

## Step 7: Prepare for Packagist

### 1. Create GitHub Repository
```bash
git init
git add .
git commit -m "Initial release"
git branch -M main
git remote add origin https://github.com/yourusername/bvn-verification-php.git
git push -u origin main
```

### 2. Create Git Tags
```bash
git tag 1.0.0
git push --tags
```

### 3. Submit to Packagist
1. Go to [Packagist.org](https://packagist.org)
2. Click "Submit"
3. Enter your GitHub repository URL
4. Wait for automatic approval

## Step 8: Installation Test

Users can now install your package with:

```bash
composer require bvn-verification/bvn-verification-php
```

Your package is now ready for the world! 🎉