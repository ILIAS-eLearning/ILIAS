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

namespace ILIAS\Dashboard\Setup;

use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;

class ilDashboardUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulateF('DELETE FROM settings WHERE keyword = %s', [ilDBConstants::T_TEXT], ['enable_block_moving']);
        $this->db->manipulate('DELETE FROM il_block_setting WHERE ' . $this->db->like('type', ilDBConstants::T_TEXT, 'pd%'));
    }

    public function step_2(): void
    {
        $this->db->insert('settings', [
            'module' => [ilDBConstants::T_TEXT, 'common'],
            'keyword' => [ilDBConstants::T_TEXT, 'disable_recommended_content'],
            'value' => [ilDBConstants::T_TEXT, '0']
        ]);
        $this->db->insert('settings', [
            'module' => [ilDBConstants::T_TEXT, 'common'],
            'keyword' => [ilDBConstants::T_TEXT, 'disable_study_programmes'],
            'value' => [ilDBConstants::T_TEXT, '0']
        ]);
        $this->db->insert('settings', [
            'module' => [ilDBConstants::T_TEXT, 'common'],
            'keyword' => [ilDBConstants::T_TEXT, 'disable_learning_sequences'],
            'value' => [ilDBConstants::T_TEXT, '0']
        ]);

        $sql = 'SELECT * FROM settings WHERE keyword = %s';
        for ($view = 0; $view <= 4; $view++) {
            if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_active_pres_view_' . $view])) === 0) {
                $this->db->insert('settings', [
                    'module' => [ilDBConstants::T_TEXT, 'common'],
                    'keyword' => [ilDBConstants::T_TEXT, 'pd_active_pres_view_' . $view],
                    'value' => [ilDBConstants::T_TEXT, serialize(['list', 'tile'])]
                ]);
            }
            if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_def_pres_view_' . $view])) === 0) {
                $this->db->insert('settings', [
                    'module' => [ilDBConstants::T_TEXT, 'common'],
                    'keyword' => [ilDBConstants::T_TEXT, 'pd_def_pres_view_' . $view],
                    'value' => [ilDBConstants::T_TEXT, 'list']
                ]);
            }
        }
        if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_active_sort_view_1'])) === 0) {
            $this->db->insert('settings', [
                'module' => [ilDBConstants::T_TEXT, 'common'],
                'keyword' => [ilDBConstants::T_TEXT, 'pd_active_sort_view_1'],
                'value' => [ilDBConstants::T_TEXT, serialize(['location', 'type', 'alphabet'])]
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_active_sort_view_3'])) === 0) {
            $this->db->insert('settings', [
                'module' => [ilDBConstants::T_TEXT, 'common'],
                'keyword' => [ilDBConstants::T_TEXT, 'pd_active_sort_view_3'],
                'value' => [ilDBConstants::T_TEXT, serialize(['location', 'alphabet'])]
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_active_sort_view_4'])) === 0) {
            $this->db->insert('settings', [
                'module' => [ilDBConstants::T_TEXT, 'common'],
                'keyword' => [ilDBConstants::T_TEXT, 'pd_active_sort_view_4'],
                'value' => [ilDBConstants::T_TEXT, serialize(['location', 'alphabet'])]
            ]);
        }

        if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_def_sort_view_1'])) === 0) {
            $this->db->insert('settings', [
                'module' => [ilDBConstants::T_TEXT, 'common'],
                'keyword' => [ilDBConstants::T_TEXT, 'pd_def_sort_view_1'],
                'value' => [ilDBConstants::T_TEXT, 'location']
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_def_sort_view_3'])) === 0) {
            $this->db->insert('settings', [
                'module' => [ilDBConstants::T_TEXT, 'common'],
                'keyword' => [ilDBConstants::T_TEXT, 'pd_def_sort_view_3'],
                'value' => [ilDBConstants::T_TEXT, 'location']
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, [ilDBConstants::T_TEXT], ['pd_def_sort_view_4'])) === 0) {
            $this->db->insert('settings', [
                'module' => [ilDBConstants::T_TEXT, 'common'],
                'keyword' => [ilDBConstants::T_TEXT, 'pd_def_sort_view_4'],
                'value' => [ilDBConstants::T_TEXT, 'location']
            ]);
        }
    }
}
