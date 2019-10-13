<?php


namespace TurtedServer\Handler;


use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use TurtedServer\Entity\Dispatch;
use TurtedServer\Server\Config;
use TurtedServer\Server\Dispatcher;
use TurtedServer\Server\Resolver;

class PushHandler extends AbstractRequestHandler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Resolver
     */
    private $resolver;

    public function __construct(Config $config, Resolver $resolver)
    {
        $this->config = $config;
        $this->resolver = $resolver;
        parent::__construct($config);
    }

    public function handlePush(ServerRequestInterface $request)
    {
        if ($request->getMethod() !== 'POST') {
            return new Response(405, ['Allow' => 'POST'], '');
        }

        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        // if no auth handler, auth is "always true"
        if ($this->config->authHandler) {
            $auth = call_user_func($this->config->authHandler, $data['auth']);
        } else {
            $auth = true;
        }

        if (!$auth) {
            return new Response(403, [], 'FORBIDDEN');
        }

        $dispatch = Dispatch::createFromData($data);
        $this->resolver->resolve($dispatch);
        $dispatcher = new Dispatcher();
        $dispatcher->dispatch($dispatch);
        $headers = $this->corsHeaders($request);

        return new Response(200, $headers, 'ok');
    }
}