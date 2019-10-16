<?php

namespace TurtedServer\Handler;

use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use RingCentral\Psr7\ServerRequest;
use TurtedServer\Keeper\ConnectionKeeper;
use TurtedServer\Keeper\UserConnectionKeeper;
use TurtedServer\Server\Config;

class ConnectionHandlerTest extends TestCase
{
    public function testConnectionHeaders()
    {
        $config = Config::fromArray([]);
        $conKeeper = new ConnectionKeeper();
        $userKeeper = new UserConnectionKeeper();
        $loop = $this->createMock(LoopInterface::class);
        $conHan = new ConnectionHandler($config, $conKeeper, $userKeeper, $loop);
        $request = new ServerRequest('GET', '/', ['Origin' => 'http://thisdomain.com']);

        $response = $conHan->handle($request);
        $headers = $response->getHeaders();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers, 'CORS header');
        $this->assertEquals(['http://thisdomain.com'], $headers['Access-Control-Allow-Origin'], 'CORS header');
        $this->assertEquals(['text/event-stream'], $headers['Content-Type'], 'Content type event-stream');
    }

    public function testConnectionWithUser()
    {
        $config = Config::fromArray(
            [
                'user_resolver' => function () {
                    return 'User';
                },
            ]
        );

        $conKeeper = new ConnectionKeeper();
        $userKeeper = new UserConnectionKeeper();
        $loop = $this->createMock(LoopInterface::class);
        $conHan = new ConnectionHandler($config, $conKeeper, $userKeeper, $loop);
        $request = new ServerRequest('GET', '/');

        $response = $conHan->handle($request);

        $this->assertEquals(1, $conKeeper->count(), 'A connection on request was added');
        $this->assertCount(1, $userKeeper->getUsers(), 'A user connection was added');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testConnectionWithoutUser()
    {
        $config = Config::fromArray([]);
        $conKeeper = new ConnectionKeeper();
        $userKeeper = new UserConnectionKeeper();
        $loop = $this->createMock(LoopInterface::class);
        $conHan = new ConnectionHandler($config, $conKeeper, $userKeeper, $loop);
        $request = new ServerRequest('GET', '/', []);

        $response = $conHan->handle($request);

        $this->assertEquals(1, $conKeeper->count(), 'A connection on request was added');
        $this->assertCount(0, $userKeeper->getUsers(), 'No user connection was added');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
