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

namespace ILIAS\Authentication\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;

class AbandonLoadDependantSessionDatabaseUpdateObjective implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulate(
            'DELETE FROM settings WHERE ' . $this->db->in(
                'keyword',
                [
                    'session_handling_type',
                    'session_max_count',
                    'session_min_idle',
                    'session_max_idle',
                    'session_max_idle_after_first_request'
                ]
            )
        );

        if ($this->db->tableExists('usr_session_log')) {
            $this->db->dropTable('usr_session_log', false);
        }

        if ($this->db->tableColumnExists('usr_session_stats', 'max_sessions')) {
            $this->db->dropTableColumn('usr_session_stats', 'max_sessions');
        }

        if ($this->db->tableColumnExists('usr_session_stats', 'closed_limit')) {
            $this->db->dropTableColumn('usr_session_stats', 'closed_limit');
        }

        if ($this->db->tableColumnExists('usr_session_stats', 'closed_idle')) {
            $this->db->dropTableColumn('usr_session_stats', 'closed_idle');
        }

        if ($this->db->tableColumnExists('usr_session_stats', 'closed_idle_first')) {
            $this->db->dropTableColumn('usr_session_stats', 'closed_idle_first');
        }
    }
}
