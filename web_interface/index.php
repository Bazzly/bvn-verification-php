<?php
require_once '../vendor/autoload.php';

use BVNVerification\BVNVerifier;
use BVNVerification\Exceptions\VerificationException;

$result = null;
$error = null;
$bvn = $_POST['bvn'] ?? '';
$name = $_POST['name'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $verifier = new BVNVerifier('demo-key', true, 'json-mock');
        $result = $verifier->verify($bvn, $name);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get available test BVNs for the dropdown
$testBVNs = [];
try {
    $verifier = new BVNVerifier('demo-key', true, 'json-mock');
    $testBVNs = $verifier->getBVNRecords();
} catch (Exception $e) {
    // Ignore errors for test data
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BVN Verification Demo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #34495e; }
        input, select { width: 100%; padding: 12px; border: 2px solid #bdc3c7; border-radius: 5px; font-size: 16px; }
        input:focus, select:focus { border-color: #3498db; outline: none; }
        button { background: #3498db; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; width: 100%; }
        button:hover { background: #2980b9; }
        .result { margin-top: 30px; padding: 20px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .test-data { background: #e8f4f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .test-data h3 { margin-bottom: 10px; color: #2c3e50; }
        .bvn-record { background: white; padding: 10px; margin: 5px 0; border-radius: 3px; border-left: 4px solid #3498db; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 BVN Verification Demo</h1>
        
        <div class="test-data">
            <h3>📋 Test BVN Records (from JSON file)</h3>
            <?php foreach ($testBVNs as $record): ?>
                <div class="bvn-record">
                    <strong>BVN:</strong> <?= htmlspecialchars($record['bvn']) ?> | 
                    <strong>Name:</strong> <?= htmlspecialchars($record['registered_name']) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="bvn">BVN (11 digits):</label>
                <input type="text" id="bvn" name="bvn" value="<?= htmlspecialchars($bvn) ?>" 
                       placeholder="Enter 11-digit BVN" maxlength="11" required>
            </div>
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" 
                       placeholder="Enter full name as registered" required>
            </div>
            
            <button type="submit">Verify BVN</button>
        </form>

        <?php if ($result): ?>
            <div class="result <?= $result->isMatch() ? 'success' : 'error' ?>">
                <h3><?= $result->isMatch() ? '✅ Verification Successful' : '❌ Verification Failed' ?></h3>
                <p><strong>BVN:</strong> <?= htmlspecialchars($bvn) ?></p>
                <p><strong>Name Provided:</strong> <?= htmlspecialchars($name) ?></p>
                <?php if ($result->getVerifiedName()): ?>
                    <p><strong>Registered Name:</strong> <?= htmlspecialchars($result->getVerifiedName()) ?></p>
                <?php endif; ?>
                <p><strong>Message:</strong> <?= htmlspecialchars($result->message ?? 'No additional information') ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="result error">
                <h3>❌ Error</h3>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <div class="result info">
            <h3>💡 Demo Information</h3>
            <p>This is a demo using <strong>JSON Mock Mode</strong>. The data is loaded from <code>mock_data/bvn_records.json</code>.</p>
            <p>To test:</p>
            <ul>
                <li>Use any BVN from the test records above</li>
                <li>Enter the exact registered name for successful verification</li>
                <li>Try a different name to see mismatch results</li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-fill form with test data when a BVN is selected from visible records
        document.addEventListener('click', function(e) {
            if (e.target.closest('.bvn-record')) {
                const record = e.target.closest('.bvn-record');
                const text = record.textContent;
                const bvnMatch = text.match(/BVN:\s*(\d+)/);
                const nameMatch = text.match(/Name:\s*([^|]+)/);
                
                if (bvnMatch && nameMatch) {
                    document.getElementById('bvn').value = bvnMatch[1].trim();
                    document.getElementById('name').value = nameMatch[1].trim();
                }
            }
        });
    </script>
</body>
</html>
