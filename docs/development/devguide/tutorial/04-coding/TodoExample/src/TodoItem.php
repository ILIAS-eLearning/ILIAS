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

class TodoItem
{
    public function __construct(
        private int $todo_id,
        private int $user_id,
        private string $title,
        private ?string $description,
        private ?string $deadline
    ) {
    }

    /**
     * Get the id of the item
     */
    public function getTodoId(): int
    {
        return $this->todo_id;
    }

    /**
     * Get the id of the usere to which the item belongs
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Get the title of the item
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get a description of the item (optional)
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the deadline of the item (optional)
     */
    public function getDeadline(): ?string
    {
        return $this->deadline;
    }

    /**
     * Get a clone with a new id
     */
    public function widthTodoId(int $todo_id): TodoItem
    {
        $clone = clone $this;
        $clone->todo_id = $todo_id;
        return $clone;
    }
}
