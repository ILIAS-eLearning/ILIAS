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

namespace ILIAS\Authentication\Setup;

use ilAuthUtils;
use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;

class AbandonCASAuthModeUpdateObjective implements ilDatabaseUpdateSteps
{
    private const TABLE_NAME = 'usr_data';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $default_auth_mode_result = $this->db->query(
            "SELECT value FROM settings WHERE module = " . $this->db->quote('common', ilDBConstants::T_TEXT) . " AND keyword = " . $this->db->quote('auth_mode', ilDBConstants::T_TEXT)
        );

        $default_auth_mode = (int) ($this->db->fetchAssoc($default_auth_mode_result)["value"] ?? ilAuthUtils::AUTH_LOCAL);

        $this->db->manipulateF(
            'UPDATE ' . self::TABLE_NAME . ' SET auth_mode = %s WHERE auth_mode = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$default_auth_mode === ilAuthUtils::AUTH_LOCAL ? 'default' : 'local', 'cas']
        );
    }

    public function step_2(): void
    {
        $settings = [
            "cas_server",
            "cas_port",
            "cas_uri",
            "cas_login_instructions",
            "cas_active",
            "cas_create_users",
            "cas_allow_local",
            "cas_user_default_role",
        ];

        $this->db->manipulate(
            "DELETE FROM settings WHERE module = " . $this->db->quote('common', ilDBConstants::T_TEXT) . " AND "
            . $this->db->in("keyword", $settings, false, ilDBConstants::T_TEXT),
        );
    }
}
