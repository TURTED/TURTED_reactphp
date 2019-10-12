<?php

namespace TurtedServer\Keeper;

use PHPUnit\Framework\TestCase;
use React\Stream\ThroughStream;
use TurtedServer\Entity\Connection;

class UserConnectionKeeperTest extends TestCase
{

    public function testAddRemove()
    {
        $keeper = new UserConnectionKeeper();
        $stream = new ThroughStream();
        $connection = new Connection($stream);
        $keeper->add('Somebody', $connection);
        $this->assertEquals(['Somebody'], $keeper->getUsers(), 'User in list');

        $connection->emit('close');

        $this->assertEquals([], $keeper->getUsers(), 'All gone');
    }
}
