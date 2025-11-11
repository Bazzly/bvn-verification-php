<?php

require_once 'vendor/autoload.php';

use BVNVerification\BVNVerifier;

echo "Testing JSON Mock Client\n";
echo "=======================\n\n";

$verifier = new BVNVerifier('test-key', true, 'json-mock');

// Test cases from JSON file
echo "Available test BVNs:\n";
$records = $verifier->getBVNRecords();
foreach ($records as $record) {
    echo "- BVN: {$record['bvn']} | Name: {$record['registered_name']}\n";
}

echo "\nTesting verification:\n";
echo "---------------------\n";

$tests = [
    ['12345678901', 'JOHN DOE'], // Exact match
    ['12345678901', 'John Doe'], // Different case
    ['12345678901', 'Wrong Name'], // Mismatch
    ['99988877766', 'FUNKE ADEBAYO'], // Another match
    ['00000000000', 'Test Name'], // Non-existent BVN
    ['123', 'Test Name'], // Invalid BVN
];

foreach ($tests as $test) {
    echo "Testing BVN: {$test[0]} with name: {$test[1]}\n";
    
    try {
        $result = $verifier->verify($test[0], $test[1]);
        
        if ($result->isMatch()) {
            echo "✅ MATCH - Verified as: " . $result->getVerifiedName() . "\n";
        } else {
            echo "❌ NO MATCH - " . $result->message . "\n";
        }
    } catch (Exception $e) {
        echo "💥 ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "---\n";
}
