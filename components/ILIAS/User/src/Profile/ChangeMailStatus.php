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

namespace ILIAS\User\Profile;

/**
 *
 * @author skergomard
 */
enum ChangeMailStatus: int
{
    public const VALIDITY_LOGIN = 300;

    case Login = 0;
    case EmailConfirmation = 1;

    public function next(): self
    {
        return match($this) {
            self::Login => self::EmailConfirmation,
            default => throw new \Exception('There is no next step')
        };
    }

    public function getValidity(\ilSetting $settings): int
    {
        return match($this) {
            self::Login => self::VALIDITY_LOGIN,
            self::EmailConfirmation => max((int) $settings->get('reg_hash_life_time'), \ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE)
        };
    }
}
