<?php

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


function setupMetrics(App $app) {
    $registry = new CollectorRegistry(new InMemory());

    $counter = $registry->registerCounter("php_todo_app", "request", "requests to the php webserver", ["method", "scheme", "path", "code"]);

    $app->add(function ($request, $response, $next) use ($counter) {
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $method = $request->getMethod();
        $path = $uri->getPath();

        $response = $next($request, $response);

        $counter->inc([$method, $scheme, $path, $response->getStatusCode()]);

        return $response;
    });

    $app->get("/metrics", function (Request $request, Response $response, array $args) use ($registry) {
        $registry->getOrRegisterCounter("php_todo_app", "metrics", "How often is the metrics endpoint called")->inc();

        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());

        $response->getBody()->write($result);
        return $response;
    });
}