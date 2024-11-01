<?php

return [
    'autoload' => [
        'psr4' => [
            'TrustswiftlyVerification' => realpath(__DIR__ . '/../src'),
            'TrustSwiftly' => realpath(__DIR__ . '/../lib/TrustSwiftly'),
        ]
    ]
];