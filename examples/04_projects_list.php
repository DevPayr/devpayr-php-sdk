<?php

require __DIR__ . '/../vendor/autoload.php';

use DevPayr\DevPayr;
use DevPayr\Exceptions\ApiResponseException;
use DevPayr\Exceptions\DevPayrException;

DevPayr::bootstrap([
    'api_key'         => 'gk_1_1764854584_8df72c5f159622ddf04c8a7e0590fb79',
    'secret'          => '123456789',
    'timeout'         => 10,
    'invalidBehavior' => 'log',
    'per_page'        => 2,
]);

try {
    $projects = DevPayr::projects()->list([
        'cursor' => "eyJwcm9qZWN0cy5pZCI6MywiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ"
    ]);

    echo "Projects:\n";
    print_r($projects);
} catch (ApiResponseException|DevPayrException $e) {
    error_log($e->getMessage());
}
