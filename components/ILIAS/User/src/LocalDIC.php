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
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileFieldsConfigurationRepository;
use ILIAS\User\Profile\Fields\UserDataRepository;
use ILIAS\User\Profile\Fields\Custom\CollectTypesObjective;
use ILIAS\User\Profile\Fields\Standard;
use ILIAS\User\Profile\ChangeListeners\CollectListenersObjective;
use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;
use ILIAS\Data\UUID\Factory as UUIDFactory;

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
        $this[UserSettingsRepository::class] = fn($c): UserSettingsRepository =>
            new UserSettingsRepository(
                $DIC['ilSetting'],
                is_readable(CollectSettingsObjective::PATH())
                    ? include CollectSettingsObjective::PATH()
                    : []
            );
        $this[StartingPointRepository::class] = fn($c): StartingPointRepository =>
            new StartingPointRepository(
                $DIC['ilUser'],
                $DIC['ilDB'],
                $DIC->logger(),
                $DIC['tree'],
                $DIC['rbacreview'],
                $DIC['rbacsystem'],
                $DIC['ilSetting'],
                $c[UserSettingsRepository::class]
            );
        $this[UserDataRepository::class] = fn($c): UserDataRepository =>
            new UserDataRepository(
                $DIC['ilDB']
            );
        $this[ProfileFieldsConfigurationRepository::class] = fn($c): ProfileFieldsConfigurationRepository =>
            new ProfileFieldsConfigurationRepository(
                $DIC['ilDB'],
                $c[UserDataRepository::class],
                new UUIDFactory(),
                is_readable(CollectTypesObjective::PATH())
                ? include CollectTypesObjective::PATH()
                : [],
                array_filter([
                    new Standard\Alias(),
                    new Standard\FirstName(),
                    new Standard\LastName(),
                    new Standard\Title(),
                    new Standard\Birthday(),
                    new Standard\Gender(),
                    new Standard\Avatar(),
                    new Standard\Roles(
                        $DIC['rbacreview']
                    ),
                    new Standard\OrganisationalUnits(),
                    new Standard\Interest(),
                    new Standard\HelpOffered(),
                    new Standard\HelpLookedFor(),
                    new Standard\Institution(),
                    new Standard\Department(),
                    new Standard\Street(),
                    new Standard\ZipCode(),
                    new Standard\City(),
                    new Standard\Country(),
                    new Standard\PhoneOffice(),
                    new Standard\PhoneHome(),
                    new Standard\PhoneMobile(),
                    new Standard\Fax(),
                    new Standard\Email(),
                    new Standard\SecondEmail(),
                    new Standard\Hobby(),
                    new Standard\ReferralComment(),
                    new Standard\Matriculation(),
                    \ilMapUtil::isActivated() ? new Standard\Location() : null
                ])
            );
        $this['profile.fields.changelisteners'] = fn($c): array =>
            is_readable(CollectListenersObjective::PATH())
                ? include CollectListenersObjective::PATH()
                : [];
    }
}
