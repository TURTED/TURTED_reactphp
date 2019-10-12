<?php

namespace TurtedServer\Server;


use PHPUnit\Framework\TestCase;
use TurtedServer\Entity\Connection;
use TurtedServer\Entity\Dispatch;
use TurtedServer\Keeper\ConnectionKeeper;
use TurtedServer\Keeper\UserConnectionKeeper;

class ResolverTest extends TestCase
{

    /**
     * @var Resolver
     */
    private $resolver;
    /**
     * @var Connection
     */
    private $aliceConn1;
    /**
     * @var Connection
     */
    private $aliceConn2;
    /**
     * @var Connection
     */
    private $charlyConn1;

    public function setUp()
    {
        $this->aliceConn1 = new Connection();
        $this->aliceConn2 = new Connection();
        $this->charlyConn1 = new Connection();

        $keeper = new ConnectionKeeper();
        $userKeeper = new UserConnectionKeeper();

        $keeper->add($this->aliceConn1);
        $keeper->add($this->aliceConn2);
        $keeper->add($this->charlyConn1);

        $userKeeper->add('Alice', $this->aliceConn1);
        $userKeeper->add('Alice', $this->aliceConn2);
        $userKeeper->add('Charly', $this->charlyConn1);

        $this->resolver = new Resolver($keeper, $userKeeper);
        parent::setUp();
    }

    public function testResolveEmpty()
    {
        $dispatch = Dispatch::createFromData(
            [
                'targets' => [],
            ]
        );

        $this->resolver->resolve($dispatch);
        $this->assertEquals([], $dispatch->getTargetConnections());
    }

    public function testResolveAlice()
    {
        $dispatch = Dispatch::createFromData(
            [
                'targets' => [
                    'users' => [
                        'Alice',
                        'Böb',
                    ],
                ],
            ]
        );

        $this->resolver->resolve($dispatch);
        $this->assertEquals([$this->aliceConn1, $this->aliceConn2], $dispatch->getTargetConnections());
    }

    public function testResolveCharly()
    {
        $dispatch = Dispatch::createFromData(
            [
                'targets' => [
                    'users' => [
                        'Böb',
                        'Charly',
                    ],
                ],
            ]
        );

        $this->resolver->resolve($dispatch);
        $this->assertEquals([$this->charlyConn1], $dispatch->getTargetConnections());
    }

    public function testResolveBroadcast()
    {
        $dispatch = Dispatch::createFromData(
            [
                'targets' => [
                    'broadcast' => true,
                ],
            ]
        );

        $this->resolver->resolve($dispatch);
        $this->assertEquals(
            [$this->aliceConn1, $this->aliceConn2, $this->charlyConn1],
            array_values($dispatch->getTargetConnections())
        );
    }
}
