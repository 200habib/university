<?php
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'faq' => [
        'path' => './assets/js/faq.js',
        'entrypoint' => true,
    ],
    'reset' => [
        'path' => './assets/js/reset.js',
        'entrypoint' => true,
    ],

    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
];
