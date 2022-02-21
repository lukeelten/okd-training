<?php
namespace Lukeelten\Php\TodoApp;


class TodoItem {
    public $id;
    public $text;
    public $done;

    public function toJson() : string {
        $item = $this->toMap();
        return json_encode($item);
    }

    public function toMap() : array {
        $item = [];
        $item["id"] = $this->id;
        $item["text"] = $this->text;
        $item["done"] = $this->done;
        return $item;
    }

    public static function fromJson(array $item) : TodoItem {
        $todo = new static;
        $todo->id = $item["id"];
        $todo->text = $item["text"];
        $todo->done = $item["done"];

        return $todo;
    }

}