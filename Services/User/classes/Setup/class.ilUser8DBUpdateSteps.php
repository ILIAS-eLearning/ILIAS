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

class ilUser8DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->modifyTableColumn(
            'usr_session',
            'session_id',
            [
                'type' => ilDBConstants::T_TEXT,
                'length' => '256'
            ]
        );
        $this->db->modifyTableColumn(
            'usr_session_stats_raw',
            'session_id',
            [
                'type' => ilDBConstants::T_TEXT,
                'length' => '256'
            ]
        );
        $this->db->modifyTableColumn(
            'usr_sess_istorage',
            'session_id',
            [
                'type' => ilDBConstants::T_TEXT,
                'length' => '256'
            ]
        );
    }
}
