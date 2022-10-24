<?php

declare(strict_types=1);

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

class ilForumDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('frm_settings') && !$this->db->tableColumnExists('frm_settings', 'stylesheet')) {
            $this->db->addTableColumn(
                'frm_settings',
                'stylesheet',
                [
                    'type' => 'integer',
                    'notnull' => true,
                    'length' => 4,
                    'default' => 0
                ]
            );
        }
    }

    public function step_2(): void
    {
        $this->db->manipulateF("UPDATE object_data SET offline = %s WHERE type = %s", ['integer', 'text'], [0, 'frm']);
    }
}
