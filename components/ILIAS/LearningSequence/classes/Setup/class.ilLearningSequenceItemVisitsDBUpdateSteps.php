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

/**
 * Creates the append-only visit log table for the adaptive learning sequence
 * player.
 *
 * In contrast to lso_item_path (a stack that only reflects the currently active
 * path), lso_item_visits keeps every visit a learner ever made within a
 * learning sequence, including branches that were entered and later abandoned
 * via "back". Each visit is stored as its own row with a monotonically
 * increasing position and a timestamp.
 */
class ilLearningSequenceItemVisitsDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    /**
     * The name of the table created by this update step.
     */
    private const TABLE_NAME = "lso_item_visits";

    /**
     * The database connection used to run the update step.
     */
    protected \ilDBInterface $db;

    /**
     * Sets the database connection used by the update step.
     */
    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Creates the item-visits table if it does not already exist.
     */
    public function step_1(): void
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = [
                'usr_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'lso_obj_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'position' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'ref_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'visited_ts' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ]
            ];
            $this->db->createTable(self::TABLE_NAME, $fields);
            $this->db->addPrimaryKey(self::TABLE_NAME, ["usr_id", "lso_obj_id", "position"]);
        }
    }
}
