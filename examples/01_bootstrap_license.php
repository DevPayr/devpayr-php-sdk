<?php

require __DIR__ . '/../vendor/autoload.php';

use DevPayr\DevPayr;

try {
    DevPayr::bootstrap([
        'license' => '019ae9f4-18b2-706b-b728-3f77c6bd9217',
        'secret' => '123456789',
        'domain' => 'localhost.test',
        'recheck' => true,
        'injectables' => false,
        'timeout' => 5,
        'invalidBehavior' => 'log',
        'onReady' => function (array|null $response) {
            echo "SDK booted successfully.\n";
            print_r($response);
        },
    ]);
} catch (\DevPayr\Exceptions\DevPayrException $e) {
    error_log($e->getMessage());
}
