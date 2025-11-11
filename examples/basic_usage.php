<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BVNVerification\BVNVerifier;
use BVNVerification\Exceptions\VerificationException;

// Initialize the verifier
$verifier = new BVNVerifier('your-api-key-here', true); // true for sandbox mode

try {
    // Basic verification
    $result = $verifier->verify('12345678901', 'John Doe');
    
    if ($result->isMatch()) {
        echo "✅ BVN verified successfully!\n";
        echo "Verified Name: " . $result->getVerifiedName() . "\n";
    } else {
        echo "❌ BVN verification failed\n";
        echo "Reason: " . ($result->message ?? 'Name does not match BVN records') . "\n";
    }
    
} catch (VerificationException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
}