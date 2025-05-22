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

namespace ILIAS\User\Setup;

use ILIAS\User\Profile\ChangeMailTokenDBRepository;

class DBUpdateSteps10 implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulate(
            'DELETE FROM usr_pref WHERE keyword = ' . $this->db->quote('hits_per_page', \ilDBConstants::T_TEXT)
        );
        $this->db->manipulate(
            'DELETE FROM settings WHERE ' . $this->db->like('keyword', \ilDBConstants::T_TEXT, '%hits_per_page%')
        );
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists(ChangeMailTokenDBRepository::TABLE_NAME, 'usr_id')) {
            $this->db->addTableColumn(
                ChangeMailTokenDBRepository::TABLE_NAME,
                'usr_id',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'notnull' => true,
                    'length' => 8
                ]
            );
        }
        if (!$this->db->tableColumnExists(ChangeMailTokenDBRepository::TABLE_NAME, 'status')) {
            $this->db->addTableColumn(
                ChangeMailTokenDBRepository::TABLE_NAME,
                'status',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'notnull' => false,
                    'length' => 1
                ]
            );
        }
        if ($this->db->tableColumnExists(ChangeMailTokenDBRepository::TABLE_NAME, 'valid_until')) {
            $this->db->renameTableColumn(
                ChangeMailTokenDBRepository::TABLE_NAME,
                'valid_until',
                'created_ts'
            );
        }
        if (!$this->db->primaryExistsByFields(ChangeMailTokenDBRepository::TABLE_NAME, ['token'])) {
            $this->db->manipulate('DELETE token1 FROM ' . ChangeMailTokenDBRepository::TABLE_NAME . ' token1 '
                . 'INNER JOIN ' . ChangeMailTokenDBRepository::TABLE_NAME . ' token2 '
                . 'WHERE token1.token = token2.token AND token1.created_ts < token2.created_ts');
            $this->db->addPrimaryKey(ChangeMailTokenDBRepository::TABLE_NAME, ['token']);
        }
    }

    public function step_3(): void
    {
        $query = 'SELECT * FROM settings WHERE module = %s AND keyword = %s';
        $result = $this->db->queryF(
            $query,
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            ['common', 'session_reminder_enabled']
        );
        $session_reminder = $result->numRows() ? (bool) $this->db->fetchAssoc($result)['value'] : false;
        $query = 'SELECT * FROM settings WHERE module = %s AND keyword = %s';
        $result = $this->db->queryF(
            $query,
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            ['common', 'session_reminder_lead_time']
        );
        $session_reminder_lead_time = $result->numRows() ? (int) $this->db->fetchAssoc($result)['value'] : null;
        if ($session_reminder && !isset($session_reminder_lead_time)) {
            $query = 'INSERT INTO settings (module, keyword, value) VALUES (%s, %s, %s)';
            $this->db->manipulateF(
                $query,
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_INTEGER],
                ['common', 'session_reminder_lead_time', \ilSessionReminder::SUGGESTED_LEAD_TIME]
            );
        }
        $query = 'DELETE FROM settings WHERE module = %s AND keyword = %s';
        $this->db->manipulateF(
            $query,
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            ['common', 'session_reminder_enabled']
        );
        $query = 'DELETE FROM usr_pref WHERE keyword = %s';
        $this->db->manipulateF(
            $query,
            [\ilDBConstants::T_TEXT],
            ['session_reminder_enabled']
        );
    }

    public function step_4(): void
    {
        $query = 'SELECT value FROM settings WHERE module = %s AND keyword = %s';
        $res = $this->db->queryF(
            $query,
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
            ['common', 'ps_login_max_attempts']
        );

        // We should adjust the usr_data values, even if the "Max. Login Attempts" are currently not set
        $max_login_attempts = min(
            (int) ($this->db->fetchAssoc($res)['value'] ?? \ilSecuritySettings::MAX_LOGIN_ATTEMPTS),
            \ilSecuritySettings::MAX_LOGIN_ATTEMPTS
        );

        $max_login_attempts_exceeded = $max_login_attempts + 1;

        $this->db->manipulateF(
            'UPDATE usr_data SET login_attempts = %s WHERE login_attempts > %s',
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$max_login_attempts_exceeded, $max_login_attempts_exceeded]
        );
    }

    public function step_5(): void
    {
        if ($this->db->tableColumnExists('personal_clipboard', 'title')) {
            $this->db->modifyTableColumn('personal_clipboard', 'title', [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 255,
                'notnull' => false
            ]);
        }
    }
}
