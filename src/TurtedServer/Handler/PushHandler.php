<?php


namespace TurtedServer\Handler;


use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use TurtedServer\Entity\Dispatch;
use TurtedServer\Server\Resolver;

class PushHandler
{
    /**
     * @var Resolver
     */
    private $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handlePush(ServerRequestInterface $request)
    {
        $body = $request->getBody()->getContents();
        if ($request->getMethod() !== 'POST') {
            return new Response(405, ['Allow' => 'POST'], '');
        }
        $data = json_decode($body, true);
        // @TODO check auth
        var_dump($data);
        $dispatch = Dispatch::createFromData($data);
        $this->resolver->resolve($dispatch);
        $dispatcher->dispatch($dispatch);

        return new Response(200, [], 'ok');
    }
}