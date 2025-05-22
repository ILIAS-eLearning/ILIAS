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

namespace ILIAS\LDAP\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;

class LDAPBindPasswordFieldMigration implements ilDatabaseUpdateSteps
{
    private ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('ldap_server_settings', 'bind_pass')) {
            $this->db->modifyTableColumn(
                'ldap_server_settings',
                'bind_pass',
                [
                    'type' => 'text',
                    'length' => 100,
                    'notnull' => false,
                    'default' => null
                ]
            );
        }
        if ($this->db->tableColumnExists('ldap_server_settings', 'role_bind_pass')) {
            $this->db->modifyTableColumn(
                'ldap_server_settings',
                'role_bind_pass',
                [
                    'type' => 'text',
                    'length' => 100,
                    'notnull' => false,
                    'default' => null
                ]
            );
        }
    }
}
