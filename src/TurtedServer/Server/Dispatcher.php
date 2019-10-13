<?php


namespace TurtedServer\Server;


use TurtedServer\Entity\Connection;
use TurtedServer\Entity\Dispatch;

class Dispatcher
{

    public function dispatch(Dispatch $dispatch)
    {
        /** @var Connection $connection */
        foreach ($dispatch->getTargetConnections() as $connection) {
            $connection->send($dispatch);
        }
    }
}