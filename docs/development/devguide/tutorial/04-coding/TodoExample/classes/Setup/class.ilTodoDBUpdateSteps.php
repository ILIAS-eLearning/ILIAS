<?php

class ilTodoDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    /**
     * Prepare the update
     * ilDBInterface should be the only dependency of the update steps
     */
    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Step 1: Creation of the table
     * Update steps must be consecutively numbered
     * ILIAS setup remembers the already executed steps
     */
    public function step_1()
    {
        if (! $this->db->tableExists('todo_items')) {
            $this->db->createTable('todo_items', [
                'todo_id' => ['type' => 'integer', 'length' => '4', 'notnull' => true],
                'user_id' => ['type' => 'integer', 'length' => '4', 'notnull' => true],
                'title' => ['type' => 'text', 'length' => '250', 'notnull' => true],
                'description' => ['type' => 'clob', 'notnull' => false],
                'deadline' => ['type' => 'date', 'notnull' => false],
            ]);

            $this->db->createSequence('todo_items');
            $this->db->addPrimaryKey('todo_items', ['todo_id']);
            $this->db->addIndex('todo_items', ['user_id'], 'i1');
        }
    }
}
