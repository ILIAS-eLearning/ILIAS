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

namespace ILIAS\components\TodoExample\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;

class TodoDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    /**
     * Prepare the update
     * ilDBInterface should be the only dependency of the update steps
     */
    public function prepare(ilDBInterface $db): void
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
