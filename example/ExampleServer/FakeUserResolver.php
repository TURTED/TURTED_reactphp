<?php


use TurtedServer\Interfaces\UserResolverInterface;

class FakeUserResolver implements UserResolverInterface
{

    private $users = [
        '',
        'Alice',
        'Bob',
        'Charlie',
        'David',
        'Eve',
        'Frank',
        'Grace',
        'Heidi',
        'Ivan',
        'Judy',
        'Mike',
        'Niaj',
        'Oscar',
        'Peggy',
        'Rupert',
        'Sybil',
        'Trent',
        'Victor',
        'Wendy',
    ];

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     */
    public function getUserForRequest(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $idx = array_rand($this->users);

        return $this->users[$idx];
    }
}