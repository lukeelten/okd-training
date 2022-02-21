<?php

namespace Lukeelten\Php\TodoApp;

use Prometheus\CollectorRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MetricsMiddleware implements MiddlewareInterface
{

    private $registry;

    private $counter;

    public function __construct(CollectorRegistry $registry) {
        $this->registry = $registry;
        $this->counter = $this->registry->registerCounter("php_todo_app", "request", "requests to the php webserver", ["method", "scheme", "path"]);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $method = $request->getMethod();
        $path = $uri->getPath();

        $this->counter->inc([$method, $scheme, $path]);

        return $handler->handle($request);
    }
}