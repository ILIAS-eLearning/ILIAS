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
        $this->db->manipulateF('DELETE FROM settings WHERE keyword = %s', ['text'], ['enable_block_moving']);
        $this->db->manipulate('DELETE FROM il_block_setting WHERE ' . $this->db->like('type', 'text', 'pd%'));
    }

    public function step_2(): void
    {
        $this->db->insert('settings', [
            'module' => ['text', 'common'],
            'keyword' => ['text', 'disable_recommended_content'],
            'value' => ['text', '0']
        ]);
        $this->db->insert('settings', [
            'module' => ['text', 'common'],
            'keyword' => ['text', 'disable_study_programmes'],
            'value' => ['text', '0']
        ]);
        $this->db->insert('settings', [
            'module' => ['text', 'common'],
            'keyword' => ['text', 'disable_learning_sequences'],
            'value' => ['text', '0']
        ]);

        $sql = "SELECT * FROM settings WHERE keyword = %s";
        for ($view = 0; $view <= 4; $view++) {
            if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_active_pres_view_' . $view])) === 0) {
                $this->db->insert('settings', [
                    'module' => ['text', 'common'],
                    'keyword' => ['text', 'pd_active_pres_view_' . $view],
                    'value' => ['text', serialize(['list', 'tile'])]
                ]);
            }
            if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_def_pres_view_' . $view])) === 0) {
                $this->db->insert('settings', [
                    'module' => ['text', 'common'],
                    'keyword' => ['text', 'pd_def_pres_view_' . $view],
                    'value' => ['text', 'list']
                ]);
            }
        }
        if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_active_sort_view_1'])) === 0) {
            $this->db->insert('settings', [
                'module' => ['text', 'common'],
                'keyword' => ['text', 'pd_active_sort_view_1'],
                'value' => ['text', serialize(['location', 'type', 'alphabet'])]
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_active_sort_view_3'])) === 0) {
            $this->db->insert('settings', [
                'module' => ['text', 'common'],
                'keyword' => ['text', 'pd_active_sort_view_3'],
                'value' => ['text', serialize(['location', 'alphabet'])]
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_active_sort_view_4'])) === 0) {
            $this->db->insert('settings', [
                'module' => ['text', 'common'],
                'keyword' => ['text', 'pd_active_sort_view_4'],
                'value' => ['text', serialize(['location', 'alphabet'])]
            ]);
        }

        if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_def_sort_view_1'])) === 0) {
            $this->db->insert('settings', [
                'module' => ['text', 'common'],
                'keyword' => ['text', 'pd_def_sort_view_1'],
                'value' => ['text', 'location']
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_def_sort_view_3'])) === 0) {
            $this->db->insert('settings', [
                'module' => ['text', 'common'],
                'keyword' => ['text', 'pd_def_sort_view_3'],
                'value' => ['text', 'location']
            ]);
        }
        if ($this->db->numRows($this->db->queryF($sql, ['text'], ['pd_def_sort_view_4'])) === 0) {
            $this->db->insert('settings', [
                'module' => ['text', 'common'],
                'keyword' => ['text', 'pd_def_sort_view_4'],
                'value' => ['text', 'location']
            ]);
        }
    }
}
