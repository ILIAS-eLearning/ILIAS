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
            $this->db->tableExists('il_cert_template')
            && !$this->db->indexExistsByFields('il_cert_template', ['background_image_path', 'currently_active'])
        ) {
            $this->db->addIndex('il_cert_template', ['background_image_path', 'currently_active'], 'i5');
        }

        if (
            $this->db->tableExists('il_cert_user_cert')
            && !$this->db->indexExistsByFields('il_cert_user_cert', ['background_image_path', 'currently_active'])
        ) {
            $this->db->addIndex('il_cert_user_cert', ['background_image_path', 'currently_active'], 'i7');
        }
    }
}
