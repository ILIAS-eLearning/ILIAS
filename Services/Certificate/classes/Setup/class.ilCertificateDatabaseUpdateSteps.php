<?php declare(strict_types=1);

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

class ilCertificateDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        if ($this->db->tableExists('il_cert_template') && $this->db->tableColumnExists('il_cert_template', 'certificate_content_bu')) {
            $this->db->dropTableColumn('il_cert_template', 'certificate_content_bu');
        }
        if ($this->db->tableExists('il_cert_user_cert') && $this->db->tableColumnExists('il_cert_user_cert', 'certificate_content_bu')) {
            $this->db->dropTableColumn('il_cert_user_cert', 'certificate_content_bu');
        }
    }
}
