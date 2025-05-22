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

namespace ILIAS\Tracking\Setup;

use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;

class ProgressBlockUpdateSteps11 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Create new export file table
     */
    public function step_1(): void
    {
        if ($this->db->tableExists('ut_progress_block')) {
            return;
        }
        $this->db->createTable('ut_progress_block', [
            'obj_id' => [
                'type' => ilDBConstants::T_TEXT,
                'length' => 4,
                'default' => 0,
                'notnull' => true
            ],
            'show_block' => [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 1,
                'default' => 0,
                'notnull' => true
            ]
        ]);
        $this->db->addPrimaryKey('ut_progress_block', ['obj_id']);
    }
}
