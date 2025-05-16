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

class ilDashboardAppEventListener implements ilAppEventListener
{
    private static ?ilDBStatement $clean_up = null;

    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        if ($event === 'deleteUser') {
            global $DIC;
            self::$clean_up ??= $DIC->database()->prepare(
                'DELETE FROM desktop_item WHERE user_id = ?',
                [ilDBConstants::T_INTEGER]
            );
            $DIC->database()->execute(self::$clean_up, [$parameter['usr_id']]);
        }
    }
}
