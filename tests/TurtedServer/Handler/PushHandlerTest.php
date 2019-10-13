<?php

namespace TurtedServer\Handler;

use PHPUnit\Framework\TestCase;
use RingCentral\Psr7\ServerRequest;
use TurtedServer\Server\Config;
use TurtedServer\Server\Resolver;

class PushHandlerTest extends TestCase
{

    public function testGet()
    {
        $config = new Config();
        $resolver = $this->createMock(Resolver::class);
        $pushHandler = new PushHandler($config, $resolver);
        $r = new ServerRequest('GET', '/push');

        $response = $pushHandler->handlePush($r);
        $this->assertEquals(405, $response->getStatusCode(), '405 on GET');
    }

    public function testForbidden()
    {
        $config = Config::fromArray(
            [
                'auth_handler' => function () {
                    return false;
                },
            ]
        );
        $resolver = $this->createMock(Resolver::class);
        $pushHandler = new PushHandler($config, $resolver);
        $r = new ServerRequest('POST', '/push');

        $response = $pushHandler->handlePush($r);
        $this->assertEquals(403, $response->getStatusCode(), '403 on Forbidden');
    }

    public function testPassAuthToAuthHandler()
    {
        $password = '12345';

        $config = Config::fromArray(
            [
                'auth_handler' => function ($auth) use ($password) {
                    return $auth['password'] === $password;
                },
            ]
        );

        $resolver = $this->createMock(Resolver::class);
        $pushHandler = new PushHandler($config, $resolver);
        $r = new ServerRequest(
            'POST', '/push', [], json_encode(
                [
                    'auth' => [
                        'password' => $password,
                    ],
                ]
            )
        );

        $response = $pushHandler->handlePush($r);
        $this->assertEquals(200, $response->getStatusCode(), '200 on correct password');
    }
}
