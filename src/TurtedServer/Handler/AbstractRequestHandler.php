<?php


namespace TurtedServer\Handler;


use Psr\Http\Message\ServerRequestInterface;
use TurtedServer\Server\Config;

class AbstractRequestHandler
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function corsHeaders(ServerRequestInterface $request)
    {
        $origin = '';
        $req = $request->getHeaders();
        if (isset($req['Origin'])) {
            $origin = $req['Origin'];
        }

        $headers = [];
        if ($this->isOriginAllowed($origin)) {
            $headers = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Headers' => '*',
            ];
        }

        return $headers;
    }

    /**
     * @param $origin
     * @return bool
     */
    protected function isOriginAllowed($origin)
    {
        if ($this->config->allowOrigin === '*') {
            return true;
        }

        // Not sure why header values are always an array, but :shrug:
        if (is_array($origin)) {
            $origin = $origin[0];
        }

        if (is_array($this->config->allowOrigin)) {
            return in_array($origin, $this->config->allowOrigin, true);
        }

        return false;
    }


}