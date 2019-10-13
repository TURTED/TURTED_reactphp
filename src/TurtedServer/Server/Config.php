<?php


namespace TurtedServer\Server;


class Config
{
    public $port;
    public $baseUrl;
    public $userResolver;
    public $allowOrigin;

    public static function fromArray($config)
    {
        $defaults = [
            'port' => 19195,
            'base_url' => '',
            'user_resolver' => null,
            'allow_origin' => '*',
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

        return $obj;
    }
}