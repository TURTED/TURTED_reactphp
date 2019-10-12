<?php

require __DIR__.'/../vendor/autoload.php';

use TurtedServer\TurtedServer;


include __DIR__.'/ExampleServer/FakeUserResolver.php';

$userResolver = new \FakeUserResolver(); // will return a random username for every new request

$server = new TurtedServer(
    [
        'port' => 19195,
        'user_resolver' => [$userResolver, 'getUserForRequest'],
        'base_url' => 'sse',
    ]
);

$server->start();

