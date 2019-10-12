<?php

namespace TurtedServer\Handler;

use PHPUnit\Framework\TestCase;
use RingCentral\Psr7\ServerRequest;
use TurtedServer\Server\Resolver;

class PushHandlerTest extends TestCase
{

    public function testGet()
    {
        $resolver = $this->createMock(Resolver::class);
        $pushHandler = new PushHandler($resolver);
        $r = new ServerRequest('GET', '/push');

        $response = $pushHandler->handlePush($r);
        $this->assertEquals($response->getStatusCode(), 405, '405 on GET');
    }
}
