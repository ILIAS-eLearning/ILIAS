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
        try {
            $this->db->modifyTableColumn(
                'usr_sess_istorage',
                'session_id',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => '256'
                ]
            );
        } catch (\Exception $e) {
            $message = "DB Hotfix 102: \n\n"
                . "We could not Update the length of the column `session_id` in the table\n"
                . "`usr_session_istorage` as the table engine is MyIsam.\n"
                . "This step will be finished after updating to ILIAS 8.\n"
                . "You could also lengthen the field manually after you ran the migration\n"
                . "to migrate to InnoDB, if you require longer session_ids.";
            global $ilLog;
            $ilLog->warning($message);
        }
    }
    public function step_2(): void
    {
        $this->db->modifyTableColumn(
            'usr_data',
            'time_limit_from',
            [
                'type' => ilDBConstants::T_INTEGER,
                'length' => '8'
            ]
        );
        $this->db->modifyTableColumn(
            'usr_data',
            'time_limit_until',
            [
                'type' => ilDBConstants::T_INTEGER,
                'length' => '8'
            ]
        );
    }

    public function step_3(): void
    {
        if (!$this->db->tableExists('usr_change_email_token')) {
            $this->db->createTable(
                'usr_change_email_token',
                [
                    'token' => [
                        'type'     => 'text',
                        'length'   => 32
                    ],
                    'new_email' => [
                        'type'     => 'text',
                        'length'   => 256
                    ],
                    'valid_until' => [
                        'type'     => 'integer',
                        'length'   => 8
                    ]
                ]
            );
        }
    }

    public function step_4(): void
    {
        if ($this->db->tableColumnExists('usr_data', 'street')) {
            $this->db->modifyTableColumn('usr_data', 'street', [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 512,
                'notnull' => false
            ]);
        }
    }
}
