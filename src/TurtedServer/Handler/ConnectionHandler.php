<?php


namespace TurtedServer\Handler;


use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use TurtedServer\Entity\Connection;
use TurtedServer\Keeper\ConnectionKeeper;
use TurtedServer\Keeper\UserConnectionKeeper;
use TurtedServer\Server\Config;

class ConnectionHandler extends AbstractRequestHandler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConnectionKeeper
     */
    private $connectionKeeper;

    /**
     * @var UserConnectionKeeper
     */
    private $userConnectionKeeper;

    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(
        Config $config,
        ConnectionKeeper $connectionKeeper,
        UserConnectionKeeper $userConnectionKeeper,
        LoopInterface $loop
    ) {
        parent::__construct($config);
        $this->config = $config;
        $this->connectionKeeper = $connectionKeeper;
        $this->userConnectionKeeper = $userConnectionKeeper;
        $this->loop = $loop;
    }

    public function handle(ServerRequestInterface $request)
    {
        $username = '';
        if ($this->config->userResolver) {
            $username = call_user_func($this->config->userResolver, $request);
        }

        $connection = new Connection();
        $this->connectionKeeper->add($connection);
        if ($username) {
            $this->userConnectionKeeper->add($username, $connection);
        }

        // Register a ping on the connection
        $pingTimer = $this->loop->addPeriodicTimer(28, [$connection, 'ping']);

        // Unregeister ping on close
        $connection->on(
            'close',
            function () use ($pingTimer) {
                $this->loop->cancelTimer($pingTimer);
            }
        );

        // $id = $request->getHeaderLine('Last-Event-ID');
        $connection->write('retry: 8000'.PHP_EOL);
        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
        ];
        $requestHeaders = $request->getHeaders();

        if (isset($requestHeaders['Origin'])) {
            $origin = $requestHeaders['Origin'];
            if ($this->isOriginAllowed($origin)) {
                $headers['Access-Control-Allow-Origin'] = $origin;
            }
        }

        return new Response(200, $headers, $connection->getStream());
    }
}