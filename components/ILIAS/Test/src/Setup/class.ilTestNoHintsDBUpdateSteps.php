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

namespace ILIAS\Test\Setup;

class ilTestNoHintsDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    private function dropCols($table, $cols): void
    {
        foreach ($cols as $col) {
            if ($this->db->tableColumnExists($table, $col)) {
                $this->db->dropTableColumn($table, $col);
            }
        }
    }

    public function step_1(): void
    {
        $this->dropCols('tst_tests', [
            'offer_question_hints',
            'highscore_hints'
        ]);
    }

    public function step_2(): void
    {
        $this->dropCols('tst_test_result', [
            'hint_count',
            'hint_points'
        ]);
    }

    public function step_3(): void
    {
        $this->dropCols('tst_pass_result', [
            'hint_count',
            'hint_points'
        ]);
    }

    public function step_4(): void
    {
        $this->dropCols('tst_result_cache', [
            'hint_count',
            'hint_points'
        ]);
    }

    public function step_5(): void
    {
        if ($this->db->tableExists('qpl_hint_tracking')) {
            $this->db->dropTable('qpl_hint_tracking');
        }
        if ($this->db->tableExists('qpl_hint_tracking_seq')) {
            $this->db->dropTable('qpl_hint_tracking_seq');
        }
    }

    public function step_6(): void
    {
        if ($this->db->tableExists('qpl_hints')) {
            $this->db->dropTable('qpl_hints');
        }
    }

    public function step_7(): void
    {
        $query = "DELETE FROM page_object WHERE parent_type = 'qht'";
        $this->db->manipulate($query);
    }

}
