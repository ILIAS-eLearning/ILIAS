<?php

class ilTodoItem
{
    private int $todo_id;
    private int $user_id;
    private string $title;
    private ?string $description;
    private ?string $deadline;

    public function __construct(
        int $todo_id,
        int $user_id,
        string $title,
        ?string $description,
        ?string $deadline
    ) {
        $this->todo_id = $todo_id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->description = $description;
        $this->deadline = $deadline;
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
    public function widthTodoId(int $todo_id): ilTodoItem
    {
        $clone = clone $this;
        $clone->todo_id = $todo_id;
        return $clone;
    }
}
