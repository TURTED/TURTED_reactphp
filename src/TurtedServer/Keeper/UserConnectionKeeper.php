<?php


namespace TurtedServer\Keeper;


use TurtedServer\Entity\Connection;

class UserConnectionKeeper
{
    private $users = [];

    public function add($username, Connection $connection)
    {
        echo 'Add conn for ', $username.PHP_EOL;

        if (!isset($this->users[$username])) {
            $this->users[$username] = [];
        }
        $this->users[$username][$connection->getId()] = $connection;
        $connection->on(
            'close',
            function () use ($username, $connection) {
                $this->remove($username, $connection);
            }
        );
    }

    public function remove($username, Connection $connection)
    {
        echo 'Remove connection '.$connection->getId().' for '.$username.PHP_EOL;
        // & or we wont work on the original array
        $connections = &$this->users[$username];
        $id = $connection->getId();
        if (isset($connections[$id])) {
            unset($connections[$id]);
        }

        if (empty($connections)) {
            unset($this->users[$username]);
        }
    }

    public function getUsers()
    {
        return array_keys($this->users);
    }

    public function getUserConnections($username)
    {
        if (isset($this->users[$username])) {
            return $this->users[$username];
        }

        return [];
    }
}