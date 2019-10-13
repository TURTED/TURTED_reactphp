<?php

namespace TurtedServer;


use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TurtedServer\Exceptions\InvalidLoggerException;
use TurtedServer\Exceptions\NotCallableException;

class TurtedServerTest extends TestCase
{
    public function testInvalidUserResolver()
    {
        $this->expectException(NotCallableException::class);
        $server = new TurtedServer(
            [
                'user_resolver' => 'something',
            ]
        );
    }

    public function testInvalidAuthHandler()
    {
        $this->expectException(NotCallableException::class);
        $server = new TurtedServer(
            [
                'auth_handler' => 'something',
            ]
        );
    }

    public function testInvalidLogger()
    {
        $this->expectException(InvalidLoggerException::class);
        $server = new TurtedServer(
            [
                'logger' => 'something',
            ]
        );
    }

    public function testValidLoggerCalledTwiceOnDefaults()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->setMethods(['warning'])->getMock();
        $logger->expects($this->exactly(2))->method('warning');
        $server = new TurtedServer(
            [
                'logger' => $logger,
            ]
        );
        $this->assertEquals(TurtedServer::class, get_class($server));
    }
}
