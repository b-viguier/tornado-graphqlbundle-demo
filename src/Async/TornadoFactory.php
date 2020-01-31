<?php

namespace App\Async;

use M6Web\Tornado\EventLoop;
use M6Web\Tornado\HttpClient;
use M6Web\Tornado\Adapter;

class TornadoFactory
{
    static public function createEventLoop(): EventLoop
    {
        return new Adapter\Tornado\EventLoop();
    }

    static public function createHttpClient(EventLoop $eventLoop): HttpClient
    {
        return new Adapter\Guzzle\HttpClient(
            $eventLoop,
            new Adapter\Guzzle\CurlMultiClientWrapper()
        );
    }
}
