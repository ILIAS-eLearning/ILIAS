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

namespace ILIAS\Poll\Setup;

use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;

class DBUpdateSteps10 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists("il_poll_image")) {
            return;
        }
        $this->db->createTable("il_poll_image", [
            'object_id' => [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 8,
                'default' => 0,
                'notnull' => true
            ],
            'rid' => [
                'type' => ilDBConstants::T_TEXT,
                'length' => 64,
                'default' => '',
                'notnull' => true
            ]
        ]);
        $this->db->addPrimaryKey("il_poll_image", ["object_id"]);
    }

    public function step_2(): void
    {
        if (
            !$this->db->tableExists("il_poll") or
            $this->db->tableColumnExists("il_poll", "migrated")
        ) {
            return;
        }
        $this->db->addTableColumn("il_poll", "migrated", [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'default' => 0
        ]);
    }
}
