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

class ilRepository9DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }


    /**
     * creates a column "rid" that is used to reference d IRSS Resource for a Profile Picture
     */
    public function step_1(): void
    {
        $this->db->manipulate(
            'DELETE FROM `settings` WHERE `keyword`="item_cmd_asynch"'
        );
    }

    public function step_2(): void
    {
        $query = 'SELECT * FROM settings WHERE module = %s AND keyword = %s';
        $result = $this->db->queryF($query, [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT], ['common', 'session_reminder_enabled']);
        $session_reminder = $result->numRows() ? (bool) $this->db->fetchAssoc($result)['value'] : false;
        if ($session_reminder) {
            $query = 'INSERT INTO settings (module, keyword, value) VALUES (%s, %s, %s)';
            $this->db->manipulateF(
                $query,
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
                ['common', 'session_reminder_lead_time', ilSessionReminder::SUGGESTED_LEAD_TIME]
            );
            $query = 'DELETE FROM settings WHERE module = %s AND keyword = %s';
            $this->db->manipulateF(
                $query,
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                ['common', 'session_reminder_enabled']
            );
        }
        $query = 'INSERT INTO settings (module, keyword, value) VALUES (%s, %s, %s)';
        $this->db->manipulateF(
            $query,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
            ['common', 'session_reminder_lead_time', ilSessionReminder::LEAD_TIME_DISABLED]
        );
    }
}
