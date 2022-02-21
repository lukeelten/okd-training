<?php

use Lukeelten\Php\TodoApp\MetricsMiddleware;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


function setupMetrics(App $app) {
    $registry = new CollectorRegistry(new InMemory());

    $middleware = new MetricsMiddleware($registry);
    $app->addMiddleware($middleware);

    $app->get("/metrics", function (Request $request, Response $response, array $args) use ($registry) {
        $registry->getOrRegisterCounter("php_todo_app", "metrics", "How often is the metrics endpoint called")->inc();

        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());

        $response->getBody()->write($result);
        return $response;
    });
}