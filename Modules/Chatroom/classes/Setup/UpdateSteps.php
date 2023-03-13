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

namespace ILIAS\Chatroom\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;

class UpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->dropTable('chatroom_prooms', false);
        $this->db->dropTable('chatroom_proomaccess', false);
        $this->db->dropTable('chatroom_psessions', false);

        $this->dropColumnWhenExists('chatroom_history', 'sub_room');
        $this->dropColumnWhenExists('chatroom_settings', 'allow_private_rooms');
        $this->dropColumnWhenExists('chatroom_history', 'private_rooms_enabled');
    }

    private function dropColumnWhenExists(string $table, string $column): void
    {
        if ($this->db->tableColumnExists($table, $column)) {
            $this->db->dropTableColumn($table, $column);
        }
    }
}
