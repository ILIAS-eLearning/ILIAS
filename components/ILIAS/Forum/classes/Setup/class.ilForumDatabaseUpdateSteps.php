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

class ilForumDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('frm_settings') && !$this->db->tableColumnExists('frm_settings', 'stylesheet')) {
            $this->db->addTableColumn(
                'frm_settings',
                'stylesheet',
                [
                    'type' => 'integer',
                    'notnull' => true,
                    'length' => 4,
                    'default' => 0
                ]
            );
        }
    }

    public function step_2(): void
    {
        $this->db->manipulateF("UPDATE object_data SET offline = %s WHERE type = %s", ['integer', 'text'], [0, 'frm']);
    }

    public function step_3(): void
    {
        if (!$this->db->tableColumnExists('frm_posts', 'rcid')) {
            $this->db->addTableColumn(
                'frm_posts',
                'rcid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }

    public function step_4(): void
    {
        if ($this->db->tableExists('frm_thread_access')) {
            $this->db->dropTable('frm_thread_access');
        }
    }

    public function step_5(): void
    {
        if ($this->db->tableExists('settings')) {
            $this->db->manipulateF(
                "DELETE FROM settings WHERE keyword = %s",
                ['text'],
                ['frm_new_deadline']
            );
        }
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('frm_posts_drafts', 'rcid')) {
            $this->db->addTableColumn(
                'frm_posts_drafts',
                'rcid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }
}
