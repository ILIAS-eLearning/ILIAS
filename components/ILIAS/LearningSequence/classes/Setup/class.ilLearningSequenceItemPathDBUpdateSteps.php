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
 * Creates the table that stores the path a learner has actually walked through
 * a learning sequence in adaptive mode.
 *
 * The path is an ordered list (a stack) of visited ref_ids per learner and
 * learning sequence. The current object is the one with the highest position.
 */
class ilLearningSequenceItemPathDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    private const TABLE_NAME = "lso_item_path";

    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

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
                ]
            ];
            $this->db->createTable(self::TABLE_NAME, $fields);
            $this->db->addPrimaryKey(self::TABLE_NAME, ["usr_id", "lso_obj_id", "position"]);
        }
    }
}
