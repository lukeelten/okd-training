<?php

namespace Lukeelten\Php\TodoApp;

use \PDO;
use Ramsey\Uuid\Uuid;

class DatabasePersistence implements TodoPersistence
{

    private $db;
    private $table;

    public function __construct(PDO $db, string $table) {
        $this->db = $db;
        $this->table = $table;
    }

    public function createSchema() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS " . $this->table . " (id VARCHAR(255), text TEXT, done BOOLEAN)");
    }

    public function listItems(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table);
        $stmt->execute();

        $result = $stmt->fetchAll();
        $items = [];

        foreach ($result as $item) {
            $items[] = TodoItem::fromJson($item);
        }

        return $items;
    }

    public function getItem(string $id): TodoItem
    {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);

        if ($stmt->rowCount() == 0) {
            return new TodoItem();
        }

        $result = $stmt->fetch();
        return TodoItem::fromJson($result);
    }

    public function createItem(string $text): TodoItem
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (id, text, done) VALUES (?, ?, ?)");
        $stmt->execute([$id, $text, false]);

        return $this->getItem($id);
    }

    public function updateItem(string $id, array $patch): TodoItem
    {
        $item = $this->getItem($id);
        if (empty($item->id)) {
            return $item;
        }

        if (isset($patch["text"])) {
            $item->text = $patch["text"];
        }

        if (isset($patch["done"])) {
            $item->done = boolval($patch["done"]);
        }

        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET text = ?, done = ? WHERE id = ? LIMIT 1");
        $stmt->execute([$item->text, $item->done, $item->id]);

        return $item;
    }

    public function deleteItem(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM " . $this->table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);

        return ($stmt->rowCount() > 0);
    }
}