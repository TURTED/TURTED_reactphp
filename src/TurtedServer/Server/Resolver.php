<?php


namespace TurtedServer\Server;


use TurtedServer\Entity\Dispatch;
use TurtedServer\Keeper\ConnectionKeeper;
use TurtedServer\Keeper\UserConnectionKeeper;

/**
 * Find target connections for a dispatch
 */
class Resolver
{
    /**
     * @var ConnectionKeeper
     */
    private $connectionKeeper;
    /**
     * @var UserConnectionKeeper
     */
    private $userConnectionKeeper;

    public function __construct(ConnectionKeeper $connectionKeeper, UserConnectionKeeper $userConnectionKeeper)
    {
        $this->connectionKeeper = $connectionKeeper;
        $this->userConnectionKeeper = $userConnectionKeeper;
    }

    public function resolve(Dispatch $dispatch)
    {
        if ($dispatch->isBroadcast()) {
            $dispatch->setTargetConnections($this->connectionKeeper->getConnections());

            return $dispatch;
        }

        // Check if user targets exist as array
        $targets = $dispatch->getTargets();
        if (!isset($targets['users'])) {
            // echo 'ERR: No users'.PHP_EOL;

            return $dispatch;
        }

        if (!is_array($targets['users'])) {
            return $dispatch;
        }

        foreach ($targets['users'] as $username) {
            // echo 'Add conn to targets for '.$username.PHP_EOL;
            $dispatch->addTargetConnections($this->userConnectionKeeper->getUserConnections($username));
        }

        return $dispatch;
    }
}