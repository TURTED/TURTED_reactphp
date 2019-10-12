<?php


namespace TurtedServer\Interfaces;


use Psr\Http\Message\ServerRequestInterface;

interface UserResolverInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    public function getUserForRequest(ServerRequestInterface $request);
}