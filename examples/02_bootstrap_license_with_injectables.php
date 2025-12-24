<?php

require __DIR__ . '/../vendor/autoload.php';

use DevPayr\DevPayr;

try {
    DevPayr::bootstrap([
        'license' => '019ae9f4-18b2-706b-b728-3f77c6bd9217',
        'secret' => '123456789',
        'domain' => 'localhost.test',
        'recheck' => true,
        'injectables' => true,
        'handleInjectables' => false,
        'injectablesPath' => __DIR__ . '/injectables',
        'injectablesVerify' => true,
        'timeout' => 10,
        'invalidBehavior' => 'log',
        'onReady' => function (array|null $response) {
            $injectables = $response['data']['injectables'] ?? [];
            echo "Validated. Injectables count: " . (is_array($injectables) ? count($injectables) : 0) . "\n";
            print_r($response);
        },
    ]);
} catch (\DevPayr\Exceptions\DevPayrException $e) {
    error_log($e->getMessage());
}
