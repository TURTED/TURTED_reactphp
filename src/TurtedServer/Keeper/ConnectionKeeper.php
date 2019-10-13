<?php


namespace TurtedServer\Keeper;


use TurtedServer\Entity\Connection;

class ConnectionKeeper
{
    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @param Connection $connection
     */
    public function add(Connection $connection)
    {
        $this->connections[$connection->getId()] = $connection;
        $connection->on(
            'close',
            function () use ($connection) {
                $this->remove($connection);
            }
        );
    }

    /**
     * @param Connection $connection
     */
    public function remove(Connection $connection)
    {
        // echo "Weg 1".PHP_EOL;
        $id = $connection->getId();
        if (isset($this->connections[$id])) {
            unset($this->connections[$id]);
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->connections);
    }

    /**
     * @return Connection[]
     */
    public function getConnections()
    {
        return $this->connections;
    }
}