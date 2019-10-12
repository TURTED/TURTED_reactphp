<?php

namespace TurtedServer\Keeper;

use PHPUnit\Framework\TestCase;
use React\Stream\ThroughStream;
use TurtedServer\Entity\Connection;

class ConnectionKeeperTest extends TestCase
{
    public function testAddRemove()
    {
        $connectionHandler = new ConnectionKeeper();
        $stream = new ThroughStream();
        $connection = new Connection($stream);
        $connectionHandler->add($connection);
        $this->assertEquals(1, $connectionHandler->count(), 'Connection in hanlder');

        $connection->emit('close');

        $this->assertEquals(0, $connectionHandler->count(), 'All gone');
    }

}
