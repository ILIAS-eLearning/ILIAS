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

class Test12DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_enabled')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_enabled');
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_anon')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_anon');
        }
    }

    public function step_3(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_achieved_ts')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_achieved_ts');
        }
    }

    public function step_4(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_score')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_score');
        }
    }

    public function step_5(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_percentage')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_percentage');
        }
    }

    public function step_6(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_wtime')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_wtime');
        }
    }

    public function step_7(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_own_table')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_own_table');
        }
    }

    public function step_8(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_top_table')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_top_table');
        }
    }

    public function step_9(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'highscore_top_num')) {
            $this->db->dropTableColumn('tst_tests', 'highscore_top_num');
        }
    }
}
