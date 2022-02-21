<?php

use Lukeelten\Php\TodoApp\FilesystemPersistence;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;


require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/todo.php';

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

// Todo Daten werden einfach in das Dateisystem geschrieben
$todoPath = getenv("TODO_PATH");
if ($todoPath == false) {
    $todoPath = "/tmp/todo";
}
$todoPersistence = new FilesystemPersistence($todoPath);

/*
// Todo Daten werden in eine Datenbank geschrieben.
$driver = getenv("DB_DRIVER");
$hostname = getenv("DB_HOST");
$database = getenv("DB_NAME");
$user = getenv("DB_USER");
$password = getenv("DB_PASSWORD");

$db = new PDO($driver . ":host=" . $hostname . ";dbname=" . $database, $user, $password);
$persistence = new DatabasePersistence($db, "todo");
$persistence->createSchema();

 */

setupTodoApi($app, $todoPersistence);


$app->run();