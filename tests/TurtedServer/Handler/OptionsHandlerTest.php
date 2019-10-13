<?php

namespace TurtedServer\Handler;

use PHPUnit\Framework\TestCase;
use RingCentral\Psr7\ServerRequest;
use TurtedServer\Server\Config;

class OptionsHandlerTest extends TestCase
{

    public function testWildcardCors()
    {
        $config = Config::fromArray(
            [
                'allow_origin' => '*',
            ]
        );
        $handler = new OptionsHandler($config);
        $request = new ServerRequest('OPTIONS', 'http://thisdomain.com/push', ['Origin' => 'http://thisdomain.com']);
        $response = $handler->handle($request);

        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers);
        $this->assertEquals(['http://thisdomain.com'], $headers['Access-Control-Allow-Origin']);
    }

    public function testRequestWithoutOriginOnAllowSpecific()
    {
        $config = Config::fromArray(
            [
                'allow_origin' => 'http://somethingelse.com',
            ]
        );
        $handler = new OptionsHandler($config);
        $request = new ServerRequest('OPTIONS', 'http://thisdomain.com/push', ['Origin' => 'http://thisdomain.com']);
        $response = $handler->handle($request);

        $headers = $response->getHeaders();
        $this->assertArrayNotHasKey('Access-Control-Allow-Origin', $headers);
    }

    public function testAllowedCors()
    {
        $config = Config::fromArray(
            [
                'allow_origin' => ['http://thisdomain.com'],
            ]
        );
        $handler = new OptionsHandler($config);
        $request = new ServerRequest('OPTIONS', 'http://thisdomain.com/push', ['Origin' => 'http://thisdomain.com']);
        $response = $handler->handle($request);

        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers);
        $this->assertEquals(['http://thisdomain.com'], $headers['Access-Control-Allow-Origin']);
    }

    public function testUnallowedCors()
    {
        $config = Config::fromArray(
            [
                'allow_origin' => ['http://otherdomain.com'],
            ]
        );
        $handler = new OptionsHandler($config);
        $request = new ServerRequest('OPTIONS', 'http://thisdomain.com/push', ['Origin' => 'http://thisdomain.com']);
        $response = $handler->handle($request);

        $headers = $response->getHeaders();
        $this->assertArrayNotHasKey('Access-Control-Allow-Origin', $headers);
    }
}
