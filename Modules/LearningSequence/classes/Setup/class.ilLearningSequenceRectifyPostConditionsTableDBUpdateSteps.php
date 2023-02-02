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

class ilLearningSequenceRectifyPostConditionsTableDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    private const TABLE_NAME = "post_conditions";

    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->dropPrimaryKey(self::TABLE_NAME);
        $this->db->addPrimaryKey(self::TABLE_NAME, ["ref_id"]);
    }

    public function step_2(): void
    {
        $this->db->modifyTableColumn(
            self::TABLE_NAME,
            "value",
            [
                "type" => "text",
                "length" => 255,
                "notnull" => false
            ]
        );
    }

    public function step_3(): void
    {
        $this->db->manipulate(
            "UPDATE " . self::TABLE_NAME . " SET value = NULL WHERE value = 0"
        );
    }
}
