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

class ilCertificateDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('il_cert_template') && $this->db->tableColumnExists('il_cert_template', 'certificate_content_bu')) {
            $this->db->dropTableColumn('il_cert_template', 'certificate_content_bu');
        }
        if ($this->db->tableExists('il_cert_user_cert') && $this->db->tableColumnExists('il_cert_user_cert', 'certificate_content_bu')) {
            $this->db->dropTableColumn('il_cert_user_cert', 'certificate_content_bu');
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableExists('il_cert_template') && $this->db->tableColumnExists('il_cert_template', 'certificate_content_backup')) {
            $this->db->dropTableColumn('il_cert_template', 'certificate_content_backup');
        }
        if ($this->db->tableExists('il_cert_user_cert') && $this->db->tableColumnExists('il_cert_user_cert', 'certificate_content_backup')) {
            $this->db->dropTableColumn('il_cert_user_cert', 'certificate_content_backup');
        }
    }

    public function step_3(): void
    {
        if ($this->db->tableExists('il_cert_bgtask_migr')) {
            $this->db->dropTable('il_cert_bgtask_migr');
        }
    }

    public function step_4(): void
    {
        if ($this->db->tableExists('il_cert_user_cert') && $this->db->tableColumnExists('il_cert_user_cert', 'user_id')) {
            $this->db->renameTableColumn('il_cert_user_cert', 'user_id', 'usr_id');
        }
    }

    public function step_5(): void
    {
        if (
            $this->db->tableExists('il_cert_template') &&
            !$this->db->indexExistsByFields('il_cert_template', ['background_image_path', 'currently_active'])
        ) {
            $this->db->addIndex('il_cert_template', ['background_image_path', 'currently_active'], 'i5');
        }

        if (
            $this->db->tableExists('il_cert_user_cert') &&
            !$this->db->indexExistsByFields('il_cert_user_cert', ['background_image_path', 'currently_active'])
        ) {
            $this->db->addIndex('il_cert_user_cert', ['background_image_path', 'currently_active'], 'i7');
        }
    }

    public function step_6(): void
    {
        if ($this->db->tableExists('il_cert_user_cert') &&
            !$this->db->tableColumnExists('il_cert_user_cert', 'certificate_id')) {
            $this->db->addTableColumn('il_cert_user_cert', 'certificate_id', [
                'type' => ilDBConstants::T_TEXT,
                'length' => 64,
                'notnull' => false,
            ]);
        }
    }

    public function step_7(): void
    {
        if ($this->db->tableExists('il_cert_user_cert') &&
            $this->db->tableColumnExists('il_cert_user_cert', 'certificate_id')) {

            try {
                $this->db->dropUniqueConstraint('il_cert_user_cert', 'c1');
            } catch (ilDatabaseException|PDOException) {
                // Nothing to do
            }

            $this->db->manipulateF(
                'UPDATE il_cert_user_cert SET certificate_id = %s WHERE certificate_id IS NULL',
                [ilDBConstants::T_TEXT],
                ['-']
            );

            $this->db->modifyTableColumn('il_cert_user_cert', 'certificate_id', [
                'type' => ilDBConstants::T_TEXT,
                'length' => 64,
                'notnull' => true,
                'default' => '-'
            ]);
        }
    }

    public function step_8(): void
    {
        $query = 'SELECT COUNT(*) cnt FROM il_cert_user_cert WHERE certificate_id = ' .
            $this->db->quote('-', ilDBConstants::T_TEXT);
        $res = $this->db->query($query);
        $num = (int) $this->db->fetchAssoc($res)['cnt'];
        if ($num === 0) {
            $this->db->addUniqueConstraint('il_cert_user_cert', ['certificate_id'], 'c1');
        }
    }

    public function step_9(): void
    {
        if (
            $this->db->tableExists('il_cert_user_cert')
        ) {
            $this->db->addTableColumn(
                'il_cert_user_cert',
                'background_image_ident',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 255,
                    'notnull' => false,
                ]
            );

            $this->db->addTableColumn(
                'il_cert_user_cert',
                'thumbnail_image_ident',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 255,
                    'notnull' => false,
                ]
            );
        }

        if (
            $this->db->tableExists('il_cert_template')
        ) {
            $this->db->addTableColumn(
                'il_cert_template',
                'background_image_ident',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 255,
                    'notnull' => false,
                ]
            );

            $this->db->addTableColumn(
                'il_cert_template',
                'thumbnail_image_ident',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 255,
                    'notnull' => false,
                ]
            );
        }

        $res = $this->db->query(
            'SELECT value FROM settings WHERE keyword = ' .
            $this->db->quote('defaultImageFileName', ilDBConstants::T_TEXT) . ' AND module = ' .
            $this->db->quote('certificate', ilDBConstants::T_TEXT)
        );
        $row = $this->db->fetchAssoc($res);
        $defaultImageFileName = $row['value'] ?? '';

        $this->db->manipulate(
            'DELETE FROM settings WHERE keyword = ' .
            $this->db->quote('defaultImageFileName', ilDBConstants::T_TEXT) . ' AND module = ' .
            $this->db->quote('certificate', ilDBConstants::T_TEXT)
        );
        $this->db->insert('settings', [
            'module' => [ilDBConstants::T_TEXT, 'certificate'],
            'keyword' => [ilDBConstants::T_TEXT, 'cert_bg_image'],
            'value' => [ilDBConstants::T_TEXT, $defaultImageFileName],
        ]);
    }
}
