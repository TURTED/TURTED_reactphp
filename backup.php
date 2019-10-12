<?php

namespace Turted\Server;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Request;
use React\Http\Response;
use React\Http\Server;
use React\Stream\ThroughStream;

class TurtedServer
{
    /**
     * @var int
     */
    private $port;

    private $connections = [];

    /**
     * @var callable
     */
    private $userResolver;

    /** @var string */
    private $baseUrl;
    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;

    public function __construct($config)
    {
        if (is_numeric($config)) {
            $this->port = (int)$config;
        } else {
            if (isset($config['port'])) {
                $this->port = (int)$config['port'];
            }
        }

        if (isset($config['user_resolver']) {
            if (is_callable($config['user_resolver'])) {
                $this->userResolver = $config['user_resolver'];
            }
        }

        if ($this->port <= 0) {
            $this->port = 19195;
        }

        if (isset($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
        }
    }

    private function handleRequest(ServerRequestInterface $request) {
        $uri = $request->getUri();
        var_dump($request->getCookieParams());
        $path = str_replace($this->baseUrl, '', $uri->getPath());
        if ($path === '') {
            $path = '/';
        }
        var_dump($path);
    }

    public function start()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $port = '0.0.0.0:'.$this->port;

        $http = new Server([$this,'handleRequest']);
            function (ServerRequestInterface $request) use ($loop, &$connections, $baseUrl) {

                if (($path === '/push') || ($path === '/push/')) {
                    // @TODO check "old style" to give info about version 4
                    if ($request->getMethod() === 'POST') {
                        $body = $request->getBody()->getContents();
                        $data = json_decode($body, true);
                        var_dump($data);
                        // $data ['time'] = date('H:i:s');
                        brodcast($data);
                    }

                    return new Response(200, ['Content-Type' => 'text/html',], 'ok');
                }

                if ($path === '/stream') {
                    $stream = new ThroughStream();
                    $stream->on(
                        'close',
                        function () use ($stream) {
                            remove($stream);
                        }
                    );
                    $id = $request->getHeaderLine('Last-Event-ID');
                    var_dump('Last ID: '.$id);
                    $connections[] = $stream;
                    $stream->write('as'.PHP_EOL);
                    $stream->write('retry: 8000'.PHP_EOL);
                    $headers = ['Cache-Control' => 'no-cache', 'Content-Type' => 'text/event-stream',];

                    return new Response(200, $headers, $stream);
                }

                return new Response(404, [], 'not found');
            }
        );

        function brodcast($data)
        {
            global $connections;
            global $id;

            $json = json_encode($data);
            /** @var ThroughStream[] $connections */
            foreach ($connections as $connection) {
                if ($connection->isWritable()) {
                    $id++;
                    $connection->write('event: ping'.PHP_EOL);
                    $connection->write('id: '.md5($id).PHP_EOL);
                    $connection->write('data: '.$json.PHP_EOL.PHP_EOL);
                }
            }
        }

        function remove($stream)
        {
            global $connections;
            echo "Weg 1".PHP_EOL;
            $pos = array_search($stream, $connections, true);
            if ($pos !== false) {
                unset($connections[$pos]);
            }
        }

        $loop->addPeriodicTimer(
            12,
            function () use (&$connections) {
                $memory = memory_get_usage() / 1024;
                $formatted = number_format($memory, 3).'K';
                echo "Current memory usage: {$formatted}\n";
                echo count($connections)." connections\n";

                $load = sys_getloadavg();
                $data = ['time' => date('H:i:s'), 'load' => $load];
                brodcast($data);
            }
        );

        $socket = new \React\Socket\Server($port, $loop);
        $http->listen($socket);
#echo 'Server now listening on http://localhost:'.$socket->getPort().PHP_EOL;

        $loop->run();
        brodcast("I'm running");
    }
}

