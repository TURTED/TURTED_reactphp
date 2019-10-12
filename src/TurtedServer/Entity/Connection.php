<?php


namespace TurtedServer\Entity;


use Evenement\EventEmitter;
use React\Stream\ThroughStream;
use Turted\Server\Dispatch;

class Connection extends EventEmitter
{
    private $id;
    /**
     * @var ThroughStream
     */
    private $stream;

    public function __construct(ThroughStream $stream = null)
    {
        if (!$stream) {
            $stream = new ThroughStream();
        }
        $this->id = uniqid('con', true);
        $this->stream = $stream;

        // when stream closes emit/trigger close on connection
        $stream->on(
            'close',
            function () use ($stream) {
                $this->emit('close');
            }
        );
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ThroughStream
     */
    public function getStream()
    {
        return $this->stream;
    }

    public function send(Dispatch $dispatch)
    {
        $this->stream->write('event: '.(string)$dispatch->getEvent().PHP_EOL);
        $this->stream->write('data: '.json_encode($dispatch->getPayload()).PHP_EOL.PHP_EOL);
    }

    public function ping()
    {
        $this->stream->write('event: ping'.PHP_EOL);
        $this->stream->write('data: {}'.PHP_EOL.PHP_EOL);
    }
}