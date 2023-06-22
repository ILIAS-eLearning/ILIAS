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

namespace ILIAS\Dashboard\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;
use ilPDSelectedItemsBlockViewSettings;
use ilObjUser;
use ilPDSelectedItemsBlockConstants;
use ilSetting;
use ILIAS\Dashboard\Access\DashboardAccess;

class ilDashboardUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulateF('DELETE FROM settings WHERE keyword = %s', ['text'], ['enable_block_moving']);
        $this->db->manipulate('DELETE FROM il_block_setting WHERE ' . $this->db->like('type', 'text', 'pd%'));
    }

    public function step_2(): void
    {
        for ($view = 0; $view <= 4; $view++) {
            $this->db->manipulateF('INSERT IGNORE INTO settings (module, keyword, value) VALUES (%s, %s, %s)', ['text', 'text', 'text'], ['common', 'pd_active_pres_view_' . $view, serialize(['list', 'tile'])]);
        }
        $this->db->manipulateF('INSERT IGNORE INTO settings (module, keyword, value) VALUES (%s, %s, %s)', ['text', 'text', 'text'], ['common', 'pd_active_sort_view_0', serialize(['location', 'type', 'alphabet'])]);
        $this->db->manipulateF('INSERT IGNORE INTO settings (module, keyword, value) VALUES (%s, %s, %s)', ['text', 'text', 'text'], ['common', 'pd_active_sort_view_1', serialize(['location', 'type', 'alphabet'])]);
        $this->db->manipulateF('INSERT IGNORE INTO settings (module, keyword, value) VALUES (%s, %s, %s)', ['text', 'text', 'text'], ['common', 'pd_active_sort_view_2', serialize(['location', 'type', 'alphabet', 'start_date'])]);
        $this->db->manipulateF('INSERT IGNORE INTO settings (module, keyword, value) VALUES (%s, %s, %s)', ['text', 'text', 'text'], ['common', 'pd_active_sort_view_3', serialize(['location', 'alphabet'])]);
        $this->db->manipulateF('INSERT IGNORE INTO settings (module, keyword, value) VALUES (%s, %s, %s)', ['text', 'text', 'text'], ['common', 'pd_active_sort_view_4', serialize(['location', 'alphabet'])]);
    }
}
