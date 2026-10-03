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

namespace ILIAS\Test\Settings\GlobalSettings;

enum UserIdentifiers: string
{
    case USER_ID = 'usr_id';
    case LOGIN = 'login';
    case EMAIL = 'email';
    case MATRICULATION = 'matriculation';
    case EXTERNAL_ACCOUNT = 'ext_account';

    public function getColumnType(): string
    {
        return match ($this) {
            self::USER_ID => \ilDBConstants::T_INTEGER,
            self::LOGIN => \ilDBConstants::T_TEXT,
            self::EMAIL => \ilDBConstants::T_TEXT,
            self::MATRICULATION => \ilDBConstants::T_TEXT,
            self::EXTERNAL_ACCOUNT => \ilDBConstants::T_TEXT,
        };
    }
}
