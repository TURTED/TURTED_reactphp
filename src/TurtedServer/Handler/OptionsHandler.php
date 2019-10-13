<?php


namespace TurtedServer\Handler;


use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use TurtedServer\Server\Config;

class OptionsHandler extends AbstractRequestHandler
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    public function handle(ServerRequestInterface $request)
    {
        $headers = $this->corsHeaders($request);

        return new Response(200, $headers, '');
    }

}