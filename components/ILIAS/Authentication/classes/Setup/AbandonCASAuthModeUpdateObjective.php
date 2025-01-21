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
use ilSetting;

class AbandonCASAuthModeUpdateObjective implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    private const TABLE_NAME = 'usr_data';

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $defaultAuthModeResult = $this->db->query(
            "SELECT value FROM settings WHERE module = 'common' AND keyword = 'auth_mode'"
        );

        $defaultAuthMode = (int) ($this->db->fetchAssoc($defaultAuthModeResult)["value"] ?? ilAuthUtils::AUTH_LOCAL);

        $this->db->manipulateF(
            'UPDATE ' . self::TABLE_NAME . ' SET auth_mode = %s WHERE auth_mode = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$defaultAuthMode === ilAuthUtils::AUTH_LOCAL ? 'default' : 'local', 'cas']
        );
    }
}
