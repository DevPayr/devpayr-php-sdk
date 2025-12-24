<?php

// examples/bootstrap.php

require __DIR__ . '/../vendor/autoload.php';

use DevPayr\DevPayr;
use DevPayr\Exceptions\DevPayrException;

// --------------------------------------------
// 🔧 Step 1: Define your SDK configuration
// --------------------------------------------
$config = [
    'license'        => '01975a4e-bc1c-72fc-a1b5-b509d8f07c75',         // Replace with actual license
    'recheck'        => true,                           // Use cache or always revalidate
    'injectables'    => false,                            // Fetch & save injectables
    'injectablesPath'=> __DIR__ . '/injectables',        // Where to store them
    'injectablesVerify' => true,                         // HMAC signature check
    'action'         => 'check_project',                           // Optional action log
    'timeout'        => 1000,                            // Optional: request timeout in ms

    // Behavior on license failure
    'invalidBehavior' => 'log', // Options: 'modal', 'redirect', 'log', 'silent'
    'redirectUrl'     => 'https://yourapp.com/upgrade', // If using 'redirect' mode,
    'handleInjectables'=> false,

    // Optional callback on success
    'onReady' => function ($info) {
        echo "✅ SDK Ready: License validated successfully\n";
        print_r($info);
    }
];

// --------------------------------------------
// 🚀 Step 2: Bootstrap DevPayr runtime
// --------------------------------------------
try {
     DevPayr::bootstrap($config);
} catch (DevPayrException $e) {
    throw new DevPayrException($e->getMessage());
}

// --------------------------------------------
// 🛠️ Step 3: Use SDK Services
// --------------------------------------------

// Example: List licenses under a project
//$projectId = 'your-project-id';
//
//try {
//    $licenses = DevPayr::licenses()->list($projectId);
//    echo "🔑 Licenses for Project {$projectId}:\n";
//    print_r($licenses);
//
//    // Example: Check if project is paid
//    $paymentStatus = DevPayr::payments()->checkWithApiKey($projectId);
//    echo "💳 Payment Status:\n";
//    print_r($paymentStatus);
//
//} catch (DevPayrException $e) {
//    echo "❌ DevPayr Error: " . $e->getMessage() . "\n";
//} catch (\Throwable $e) {
//    echo "❌ General Error: " . $e->getMessage() . "\n";
//}
