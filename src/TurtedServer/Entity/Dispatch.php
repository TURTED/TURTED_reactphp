<?php


namespace TurtedServer\Entity;


class Dispatch
{
    private $event;
    /**
     * @var array
     */
    private $targets;
    /**
     * @var array
     */
    private $payload;
    private $auth;

    /**
     * @var Connection[]
     */
    private $targetConnections = [];

    public function __construct($event, array $targets, array $payload, $auth)
    {
        $this->event = $event;
        $this->targets = $targets;
        $this->payload = $payload;
        $this->auth = $auth;
    }

    /**
     * @param array $data
     * @return Dispatch
     */
    public static function createFromData(array $data)
    {
        $defaults = [
            'event' => '',
            'targets' => [],
            'payload' => [],
            'auth' => [],
        ];

        $data = array_merge($defaults, $data);
        $dispatch = new Dispatch($data['event'], $data['targets'], $data['payload'], $data['auth']);

        return $dispatch;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return mixed
     */
    public function getAuth()
    {
        return $this->auth;
    }

    public function isBroadcast()
    {
        return isset($this->targets['broadcast']);
    }

    /**
     * @param Connection[] $connections
     */
    public function setTargetConnections($connections)
    {
        $this->targetConnections = $connections;
    }

    /**
     * @param Connection $connection
     */
    public function addTargetConnection(Connection $connection)
    {
        $this->targetConnections[] = $connection;
    }

    /**
     * @return Connection[]
     */
    public function getTargetConnections()
    {
        return $this->targetConnections;
    }

    /**
     * @param Connection[] $connections
     */
    public function addTargetConnections(array $connections)
    {
        foreach ($connections as $connection) {
            $this->targetConnections[] = $connection;
        }
    }
}