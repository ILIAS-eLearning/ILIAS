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

namespace ILIAS\Forum\Setup;

use ilDBConstants;
use ilDBInterface;
use ilDatabaseUpdateSteps;

class ForumDatabaseUpdateSteps12 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        foreach (['frm_settings', 'frm_notification'] as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            if ($this->db->tableColumnExists($table, 'user_toggle_noti')) {
                $this->db->manipulate(
                    "UPDATE {$table} SET user_toggle_noti = 1 - user_toggle_noti"
                );
            }

            if ($this->db->tableColumnExists($table, 'admin_force_noti')) {
                $this->db->renameTableColumn($table, 'admin_force_noti', 'container_enforces_noti');
            }

            if ($this->db->tableColumnExists($table, 'user_toggle_noti')) {
                $this->db->renameTableColumn($table, 'user_toggle_noti', 'member_may_disable_noti');
            }
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableExists('frm_notification') &&
            !$this->db->tableColumnExists('frm_notification', 'user_deactivated_noti')) {
            $this->db->addTableColumn('frm_notification', 'user_deactivated_noti', [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 1,
                'default' => '0',
                'notnull' => true
            ]);
        }
    }
}
