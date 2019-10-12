<?php

namespace TurtedServer\Entity;


use PHPUnit\Framework\TestCase;

class DispatchTest extends TestCase
{

    public function testCreateFromData()
    {
        $dispatch = Dispatch::createFromData(['weird value']);
        $this->assertEquals(false, $dispatch->isBroadcast());
        $this->assertEquals('', $dispatch->getEvent());
        $this->assertEquals([], $dispatch->getTargets());
        $this->assertEquals([], $dispatch->getPayload());
    }
}
