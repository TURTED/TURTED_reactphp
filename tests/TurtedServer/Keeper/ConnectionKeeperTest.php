<?php

namespace TurtedServer\Keeper;

use PHPUnit\Framework\TestCase;
use TurtedServer\Entity\Connection;

class ConnectionKeeperTest extends TestCase
{
    public function testAddRemove()
    {
        $connectionKeeper = new ConnectionKeeper();
        $connection = new Connection();
        $connectionKeeper->add($connection);
        $this->assertEquals(1, $connectionKeeper->count(), 'Connection in handler');

        $connection->emit('close');

        $this->assertEquals(0, $connectionKeeper->count(), 'All gone');
    }

    public function testFlood()
    {
        $count = 1000;
        $connectionKeeper = new ConnectionKeeper();

        $connections = [];
        for ($i = 0; $i < $count; $i++) {
            $connection = new Connection();
            $connectionKeeper->add($connection);
            $connections[] = $connection;
        }

        $this->assertEquals($count, $connectionKeeper->count(), 'All there');

        while (count($connections) > 0) {
            $idx = array_rand($connections);
            $c = $connections[$idx];
            unset($connections[$idx]);
            $c->emit('close');
            // echo 'Closed '.$c->getId().PHP_EOL;
        }

        $this->assertEquals(0, $connectionKeeper->count(), 'All gone');
    }
}
