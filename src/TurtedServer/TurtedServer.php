<?php

namespace TurtedServer;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Request;
use React\Http\Response;
use React\Http\Server;
use React\Stream\ThroughStream;
use TurtedServer\Entity\Connection;
use TurtedServer\Handler\OptionsHandler;
use TurtedServer\Handler\PushHandler;
use TurtedServer\Keeper\ConnectionKeeper;
use TurtedServer\Keeper\UserConnectionKeeper;
use TurtedServer\Server\Config;
use TurtedServer\Server\Resolver;

class TurtedServer
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @var ConnectionKeeper
     */
    private $connectionKeeper;

    /**
     * @var UserConnectionKeeper
     */
    private $userConnectionKeeper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Resolver
     */
    private $resolver;

    public function __construct(array $config = [])
    {
        if (isset($config['user_resolver'])) {
            if (!is_callable($config['user_resolver'])) {
                throw new \Exception('Given user resolver is not callable');
            }
        }

        $this->config = Config::fromArray($config);
        if (!$this->config->userResolver) {
            echo 'No User Resolver configured. Server will not be able to handle names connections'.PHP_EOL.PHP_EOL;
        }

        $this->connectionKeeper = new ConnectionKeeper();
        $this->userConnectionKeeper = new UserConnectionKeeper();
        $this->resolver = new Resolver($this->connectionKeeper, $this->userConnectionKeeper);
    }

    private function handleRequest(ServerRequestInterface $request)
    {
        $uri = $request->getUri();
        $path = str_replace($this->config->baseUrl, '', $uri->getPath());
        $path = str_replace('//', '/', $path);
        if ($path === '') {
            $path = '/';
        }

        if ($request->getMethod() === 'OPTIONS') {
            $handler = new OptionsHandler($this->config);

            return $handler->handle($request);
        }

        // if it is a call to /push, handle it. Everything else will go to event stream
        if (($path === '/push') || ($path === '/push/')) {
            $pushHandler = new PushHandler($this->config, $this->resolver);

            return $pushHandler->handlePush($request);
        }



        $username = '';
        if ($this->config->userResolver) {
            $username = call_user_func($this->config->userResolver, $request);
            echo 'Username: '.$username.PHP_EOL;
        }

        $connection = new Connection();
        $this->connectionKeeper->add($connection);
        if ($username) {
            $this->userConnectionKeeper->add($username, $connection);
        }

        // Register a ping on the connection
        $pingTimer = $this->loop->addPeriodicTimer(28, [$connection, 'ping']);

        $connection->on(
            'close',
            function () use ($pingTimer) {
                $this->loop->cancelTimer($pingTimer);
            }
        );


        $id = $request->getHeaderLine('Last-Event-ID');
        var_dump('Last ID: '.$id);
        $connection->write('as'.PHP_EOL);
        $connection->write('retry: 8000'.PHP_EOL);
        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
        ];
        $origin = $request->getHeaders()['Origin'];

        // if ($this->isOriginAllowed($origin)) {
        $headers['Access-Control-Allow-Origin'] = $origin;

        // }

        return new Response(200, $headers, $connection->getStream());
    }

    public function start()
    {
        $this->loop = Factory::create();
        $port = '0.0.0.0:'.$this->config->port;

        $http = new Server(
            function (ServerRequestInterface $request) {
                try {
                    return $this->handleRequest($request);
                } catch (\Exception $exception) {
                    echo 'Exception: '.$exception->getMessage().PHP_EOL;
                }
            }
        );

        $socket = new \React\Socket\Server($port, $this->loop);
        $http->listen($socket);
        echo 'Server now listening on http://localhost:'.$port.PHP_EOL;
        $this->loop->addPeriodicTimer(12, [$this, 'info']);
        $this->info();
        $this->loop->run();
    }

    public function info()
    {
        $memory = memory_get_usage() / 1024;
        $formatted = number_format($memory, 3).'K';
        echo "Current memory usage: {$formatted}\n";
        echo $this->connectionKeeper->count()." connections\n";


        $users = $this->userConnectionKeeper->getUsers();
        foreach ($users as $user) {
            $connections = $this->userConnectionKeeper->getUserConnections($user);
            echo $user.': '.count($connections).PHP_EOL;
            /** @var ThroughStream $stream */
            foreach ($connections as $connection) {
                echo '  - '.$connection->getId().' Write: '.$connection->getStream()->isWritable().PHP_EOL;
            }
        }

    }

    /**
     * @return \React\EventLoop\LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }
}

