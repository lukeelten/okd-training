<?php
namespace Lukeelten\Php\TodoApp;

use Ramsey\Uuid\Uuid;


class FilesystemPersistence implements TodoPersistence {

    private $basePath;

    public function __construct($basePath) {
        $this->basePath = $basePath;
        @mkdir($basePath);
    }

    public function listItems() : array {
        $items = [];

        $files = scandir($this->basePath);
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }

            $content = file_get_contents($this->basePath . "/" . $file);
            $item = json_decode($content);
            $items[] = TodoItem::fromJson($item);
        }

        return $items;
    }

    public function getItem($id) : TodoItem {
        if (!is_file($this->basePath . "/" . $id)) {
            return new TodoItem();
        }

        $content = file_get_contents($this->basePath . "/" . $id);
        $item = json_decode($content);
        return TodoItem::fromJson($item);
    }

    public function createItem(string $text) : TodoItem {
        $item = new TodoItem();
        $item->id = Uuid::uuid4()->toString();
        $item->text = $text;
        $item->done = false;

        $this->saveItem($item);
        return $item;
    }

    public function updateItem(string $id, array $patch) : TodoItem {
        $item = $this->getItem($id);
        if (empty($item->id)) {
            return $item;
        }

        if (isset($patch["text"])) {
            $item->text = $patch["text"];
        }

        if (isset($patch["done"])) {
            $item->done = $patch["done"];
        }

        $this->saveItem($item);
        return $item;
    }

    private function saveItem(TodoItem $item) {
        $content = $item->toJson();
        file_put_contents($this->basePath . "/" . $item->id, $content);
    }

    public function deleteItem(string $id) : bool {
        return unlink($this->basePath . "/" . $id);
    }


}