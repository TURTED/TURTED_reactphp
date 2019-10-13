<?php


namespace TurtedServer\Entity;


use Evenement\EventEmitter;
use React\Stream\ThroughStream;

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
        echo 'Dispatch to '.$this->id.' ';
        if ((string)$dispatch->getEvent() === '') {
            echo ' cancelled'.PHP_EOL;

            return;
        }
        $this->write('event: '.(string)$dispatch->getEvent().PHP_EOL);
        echo (string)$dispatch->getEvent().PHP_EOL;
        $this->write('data: '.json_encode($dispatch->getPayload()).PHP_EOL.PHP_EOL);
    }

    public function ping()
    {
        $this->write('event: ping'.PHP_EOL);
        $this->write('data: {}'.PHP_EOL.PHP_EOL);
    }

    public function write($data)
    {
        $this->stream->write($data);
    }
}