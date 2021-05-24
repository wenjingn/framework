<?php
require 'autoload.php';

use Http\Request\Gen;

$gen = new Gen(10);
$gen->import([
    'http' => [
        'www.httpbin.org' => [
            '/bin' => null,
        ],
        'bbcash-api' => [
            '/api/dev/echoJson' => [
                [
                    'get' => [
                        'id' => 1
                    ]
                ],
            ]
        ],
    ],'
]);
