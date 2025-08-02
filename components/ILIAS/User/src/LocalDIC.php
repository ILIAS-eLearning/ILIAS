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

namespace ILIAS\User;

use ILIAS\User\Settings\User\Repository as UserSettingsRepository;
use ILIAS\User\Settings\StartingPoint\Repository as StartingPointRepository;
use ILIAS\User\Settings\User\CollectSettingsObjective;
use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;

class LocalDIC extends PimpleContainer
{
    private static ?LocalDIC $dic = null;

    public static function dic(): self
    {
        if (self::$dic === null) {
            global $DIC;
            self::$dic = new LocalDIC();
            self::$dic->init($DIC);
        }

        return self::$dic;
    }

    private function init(ILIASContainer $DIC): void
    {
        $this['settings.user.repository'] = fn($c): UserSettingsRepository =>
            new UserSettingsRepository(
                $DIC['ilSetting'],
                is_readable(CollectSettingsObjective::PATH())
                    ? include CollectSettingsObjective::PATH()
                    : []
            );
        $this['settings.starting_point.repository'] = fn($c): StartingPointRepository =>
            new StartingPointRepository(
                $DIC['ilUser'],
                $DIC['ilDB'],
                $DIC->logger(),
                $DIC['tree'],
                $DIC['rbacreview'],
                $DIC['rbacsystem'],
                $DIC['ilSetting'],
                $c['settings.user.repository']
            );
    }
}
