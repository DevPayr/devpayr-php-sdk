<?php

require __DIR__ . '/../vendor/autoload.php';

use DevPayr\DevPayr;
use DevPayr\Exceptions\ApiResponseException;

DevPayr::bootstrap([
    'license'         => '019ae9f4-18b2-706b-b728-3f77c6bd9217',
    'secret'          => '123456789',
    'domain'          => 'yourapp.com',
    'injectables'     => true,
    'timeout'         => 10,
    'invalidBehavior' => 'log',
]);

try {
    $payload = DevPayr::injectables()->stream();

    echo "Stream payload:\n";
    print_r($payload);
} catch (ApiResponseException|\DevPayr\Exceptions\DevPayrException $e) {
    error_log($e->getMessage());
}
