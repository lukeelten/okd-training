<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Lukeelten\Php\TodoApp\TodoPersistence;
use Slim\App;

function setupTodoApi(App $app, TodoPersistence $persistence) {
    $app->get('/api/v1/todo', function (Request $request, Response $response, array $args) use ($persistence) {
        $items = $persistence->listItems();

        $body = [];
        foreach ($items as $item) {
            $body[] = $item->toMap();
        }

        $json = json_encode($body);

        $response = $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
        
        $response->getBody()->write($json);

        return $response;
    });


    $app->post('/api/v1/todo', function (Request $request, Response $response, array $args) use ($persistence) {
        $parsedBody = $request->getParsedBody();

        $text = $parsedBody["text"];
        if (empty($text)) {
            return $response->withStatus(400);
        }

        $item = $persistence->createItem($text);
        $response = $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json');

        $response->getBody()->write($item->toJson());

        return $response;
    });

    $app->get('/api/v1/todo/{id}', function (Request $request, Response $response, array $args) use ($persistence) {
        $id = $args["id"];
        $item = $persistence->getItem($id);
        if (empty($item->id)) {
            return $response->withStatus(404);
        }

        $response = $response->withStatus(200)
        ->withHeader('Content-Type', 'application/json');
        
        $response->getBody()->write($item->toJson());

        return $response;
    });


    $app->delete('/api/v1/todo/{id}', function (Request $request, Response $response, array $args)  use ($persistence) {
        $id = $args["id"];

        $result = $persistence->deleteItem($id);
        if ($result) {
            return $response->withStatus(204);
        }

        return $response->withStatus(404);
    });

    $app->patch('/api/v1/todo/{id}', function (Request $request, Response $response, array $args)  use ($persistence) {
        $id = $args["id"];

        $body = $request->getParsedBody();
        $item = $persistence->updateItem($id, $body);

        if (empty($item->id)) {
            return $response->withStatus(404);
        }

        $response = $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($item->toJson());

        return $response;
    });

}