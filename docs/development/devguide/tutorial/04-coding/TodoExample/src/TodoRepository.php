<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\components\ToDoExample;

use ilDBInterface;

class TodoRepository
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Get the items of a user
     * @return ToDoItem[]
     */
    public function getItemsOfUser(int $user_id): array
    {
        $items = [];

        $query = "SELECT * FROM todo_items WHERE user_id = %s";

        $result = $this->db->queryF($query, ['integer'], [$user_id]);

        while ($row = $this->db->fetchAssoc($result)) {
            $items[] = new TodoItem(
                $row['item_id'],
                $row['user_id'],
                $row['title'],
                $row['description'] ?? null,
                $row['deadline'] ?? null
            );
        }
        return $items;
    }

    /**
     * Create an item in the database
     * The returned item has an automatically created id
     */
    public function createItem(TodoItem $item): TodoItem
    {
        $todo_id = $this->db->nextId('todo_items');

        $this->db->insert('todo_items', [
            'todo_id' => ['integer', $todo_id],
            'user_id' => ['integer', $item->getUserId()],
            'title' => ['text', $item->getTitle()],
            'description' => ['clob', $item->getDescription()],
            'deadline' => ['date', $item->getDeadline()]
        ]);

        return $item->widthTodoId($todo_id);
    }

    /**
     * Update an item in the database
     */
    public function updateItem(TodoItem $item): void
    {
        $this->db->update('todo_items', [
            'user_id' => ['integer', $item->getUserId()],
            'title' => ['text', $item->getTitle()],
            'description' => ['clob', $item->getDescription()],
            'deadline' => ['date', $item->getDeadline()]
        ], [
            'todo_id' => ['integer', $item->getTodoId()]
        ]);

    }


    public function deleteItem(ToDoItem $item): void
    {
        $query = "DELETE FROM todo_items WHERE todo_id = %s";

        $this->db->manipulateF($query, ['integer'], [$item->getTodoId()]);
    }
}
