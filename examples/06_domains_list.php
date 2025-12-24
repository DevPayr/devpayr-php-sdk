<?php

require __DIR__ . '/../vendor/autoload.php';

use DevPayr\DevPayr;
use DevPayr\Exceptions\ApiResponseException;
use DevPayr\Exceptions\DevPayrException;

$projectId = 2;

DevPayr::bootstrap([
    'api_key'         => 'gk_1_1764854584_8df72c5f159622ddf04c8a7e0590fb79',
    'secret'          => '123456789',
    'timeout'         => 10,
    'invalidBehavior' => 'log',
]);

try {
    $domains = DevPayr::domains()->list($projectId);

    echo "Domains for project {$projectId}:\n";
    print_r($domains);
} catch (ApiResponseException|DevPayrException $e) {
    error_log($e->getMessage());
}
