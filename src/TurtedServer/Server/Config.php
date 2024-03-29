<?php


namespace TurtedServer\Server;


use Psr\Log\LoggerInterface;

class Config
{
    /** @var integer */
    public $port;

    /** @var string */
    public $baseUrl;

    /** @var callable|null */
    public $userResolver;

    /** @var array|string */
    public $allowOrigin;

    /** @var callable|null */
    public $authHandler;

    /** @var LoggerInterface */
    public $logger;

    public static function fromArray($config)
    {
        $defaults = [
            'port' => 19195,
            'base_url' => '',
            'user_resolver' => null,
            'allow_origin' => '*',
            'auth_handler' => null,
            'logger' => null,
        ];

        $config = array_merge($defaults, $config);

        $obj = new self();
        $obj->port = (int)$config['port'];
        if ($obj->port <= 0) {
            $obj->port = 19195;
        }

        $obj->baseUrl = (string)$config['base_url'];
        $obj->userResolver = $config['user_resolver'];
        $obj->allowOrigin = $config['allow_origin'];
        $obj->authHandler = $config['auth_handler'];
        $obj->logger = $config['logger'];

        return $obj;
    }
}