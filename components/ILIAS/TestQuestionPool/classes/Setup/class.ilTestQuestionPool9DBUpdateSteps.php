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

class ilTestQuestionPool9DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('qpl_questionpool', 'nav_taxonomy')) {
            $this->db->dropTableColumn('qpl_questionpool', 'nav_taxonomy');
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableColumnExists('qpl_questions', 'working_time')) {
            $this->db->dropTableColumn('qpl_questions', 'working_time');
        }
    }

    public function step_3(): void
    {
        if ($this->db->tableExists('qpl_sol_sug')) {
            $this->db->manipulateF("DELETE FROM qpl_sol_sug WHERE type = %s", ['text'], ['text']);
        }
    }

    public function step_4(): void
    {
        $query = '
            UPDATE object_data
            INNER JOIN qpl_questionpool ON object_data.obj_id = qpl_questionpool.obj_fi
            SET object_data.offline = IF(qpl_questionpool.isonline = 1, 0, 1)
            WHERE object_data.type = %s
        ';

        $this->db->manipulateF(
            $query,
            [ilDBConstants::T_TEXT],
            ['qpl']
        );

        if ($this->db->tableColumnExists('qpl_questionpool', 'isonline')) {
            $this->db->dropTableColumn('qpl_questionpool', 'isonline');
        }
    }
}
