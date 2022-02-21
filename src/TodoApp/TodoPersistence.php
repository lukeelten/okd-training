<?php
namespace Lukeelten\Php\TodoApp;

interface TodoPersistence {

    public function listItems() : array;
    public function getItem(string $id) : TodoItem;

    public function createItem(string $text) : TodoItem;

    public function updateItem(string $id, array $patch) : TodoItem;

    public function deleteItem(string $id) : bool;
}