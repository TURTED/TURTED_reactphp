<?php


namespace TurtedServer\Server;


use TurtedServer\Entity\Dispatch;

class Dispatcher
{

    public function dispatch(Dispatch $dispatch) {
        foreach ($dispatch->getConnections as $connection)
    }

}