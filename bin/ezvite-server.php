<?php
//require __DIR__ . '/vendor/autoload.php';
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use EzVite\socket;

require dirname(__DIR__) . '/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new socket()
        )
    ),
    8080
);

$server->run();