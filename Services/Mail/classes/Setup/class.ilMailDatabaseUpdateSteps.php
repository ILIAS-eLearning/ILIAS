<?php

declare(strict_types=1);

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

class ilMailDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('mail') && $this->db->tableColumnExists('mail', 'm_email')) {
            $this->db->dropTableColumn('mail', 'm_email');
        }
    }

    public function step_2(): void
    {
        $result = $this->db->queryF('SELECT value FROM settings WHERE module = %s AND keyword = %s', ['text', 'text'], ['common', 'mail_system_sys_signature']);
        $row = $this->db->fetchAssoc($result);
        if (isset($row['value']) && $row['value'] !== '') {
            $new_value = str_replace(['[CLIENT_NANE]', '[CLIENT_DESC]', '[CLIENT_URL]'], ['[INSTALLATION_NAME]', '[INSTALLATION_DESC]', '[ILIAS_URL]'], $row['value']);
            if ($new_value !== $row['value']) {
                $this->db->manipulateF(
                    'UPDATE settings SET value = %s WHERE module = %s AND keyword = %s',
                    ['text', 'text', 'text'],
                    [$new_value, 'common', 'mail_system_sys_signature']
                );
            }
        }
    }

    public function step_3(): void
    {
        $result = $this->db->query("SELECT tpl_id, m_message FROM mail_man_tpl WHERE m_message LIKE '%[CLIENT_NAME]%'");
        while ($row = $this->db->fetchAssoc($result)) {
            if (isset($row['m_message'], $row['tpl_id']) && $row['m_message'] !== '' && $row['tpl_id'] !== '') {
                $new_value = str_replace('[CLIENT_NANE]', '[INSTALLATION_NAME]', $row['m_message']);
                if ($new_value !== $row['m_message']) {
                    $this->db->manipulateF(
                        'UPDATE mail_man_tpl SET m_message = %s WHERE tpl_id = %s',
                        ['text', 'text'],
                        [$new_value, $row['tpl_id']]
                    );
                }
            }
        }
    }

    public function step_4(): void
    {
        $result = $this->db->query("SELECT lang, type, body FROM mail_template WHERE body LIKE '%[CLIENT_NAME]%'");
        while ($row = $this->db->fetchAssoc($result)) {
            if (isset($row['lang'], $row['type'], $row['body']) && $row['body'] !== '') {
                $new_value = str_replace('[CLIENT_NANE]', '[INSTALLATION_NAME]', $row['body']);
                if ($new_value !== $row['body']) {
                    $this->db->manipulateF(
                        'UPDATE mail_template SET body = %s WHERE lang = %s AND type = %s',
                        ['text', 'text', 'text'],
                        [$new_value, $row['lang'], $row['type']]
                    );
                }
            }
        }
    }

    public function step_5(): void
    {
        if ($this->db->tableExists('mail_options') && $this->db->tableColumnExists('mail_options', 'linebreak')) {
            $this->db->dropTableColumn('mail_options', 'linebreak');
        }
    }
}
