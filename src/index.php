<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;


require __DIR__ . '/../vendor/autoload.php';

AppFactory::setSlimHttpDecoratorsAutomaticDetection(false);
ServerRequestCreatorFactory::setSlimHttpDecoratorsAutomaticDetection(false);

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello OpenShift");
    return $response;
});

$app->get('/hello', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello OpenShift from PHP Application");
    return $response;
});

$app->run();