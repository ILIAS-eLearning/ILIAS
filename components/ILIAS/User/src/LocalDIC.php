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

use ILIAS\User\Search\EndpointFactory;
use ILIAS\User\Search\Search;
use ILIAS\User\Settings\Settings as UserSettings;
use ILIAS\User\Settings\SettingsImplementation as UserSettingsImplementation;
use ILIAS\User\Settings\NewAccountMail\Repository as NewAccountMailRepository;
use ILIAS\User\Settings\ConfigurationRepository as UserSettingsConfigurationRepository;
use ILIAS\User\Settings\DatabaseConfigurationRepository as DatatabaseUserSettingsConfigurationRepository;
use ILIAS\User\Settings\DataRepository as UserSettingsDataRepository;
use ILIAS\User\Settings\DatabaseDataRepository as DatatabaseUserSettingsDataRepository;
use ILIAS\User\Settings\StartingPoint\Repository as StartingPointRepository;
use ILIAS\User\Settings\CollectSettingsObjective;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Profile\ProfileImplementation;
use ILIAS\User\Profile\Fields\CachedConfigurationRepository as DatabaseProfileFieldsConfigurationRepository;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileFieldsConfigurationRepository;
use ILIAS\User\Profile\DataRepository as ProfileDataRepository;
use ILIAS\User\Profile\DatabaseDataRepository as DatabaseProfileDataRepository;
use ILIAS\User\Profile\Fields\Custom\CollectTypesObjective;
use ILIAS\User\Profile\Fields\Standard;
use ILIAS\User\Profile\ChangeListeners\CollectListenersObjective;
use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;
use ILIAS\Data\Factory as DataFactory;
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
        $this[UserSettingsConfigurationRepository::class] = fn($c): UserSettingsConfigurationRepository =>
            new DatatabaseUserSettingsConfigurationRepository(
                $DIC['ilSetting'],
                is_readable(CollectSettingsObjective::PATH())
                    ? include CollectSettingsObjective::PATH()
                    : []
            );
        $this[UserSettings::class] = fn($c): UserSettings =>
            new UserSettingsImplementation(
                $DIC['lng'],
                $DIC['ilSetting'],
                $DIC['ui.factory'],
                $DIC['refinery'],
                $c[UserSettingsConfigurationRepository::class],
                $c[UserSettingsDataRepository::class]
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
                $c[UserSettingsConfigurationRepository::class]
            );
        $this[UserSettingsDataRepository::class] = fn($c): UserSettingsDataRepository =>
            new DatatabaseUserSettingsDataRepository(
                $DIC['ilDB']
            );
        $this[ProfileDataRepository::class] = fn($c): ProfileDataRepository =>
            new DatabaseProfileDataRepository(
                $DIC['ilDB'],
                $DIC['resource_storage'],
                $c[ProfileFieldsConfigurationRepository::class]
            );
        $this[ProfileFieldsConfigurationRepository::class] = fn($c): ProfileFieldsConfigurationRepository =>
            new DatabaseProfileFieldsConfigurationRepository(
                $DIC['ilDB'],
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
                    new Standard\Avatar(
                        $DIC['resource_storage'],
                        $DIC['upload'],
                        $DIC['http']->wrapper()->post(),
                        $DIC['ui.renderer'],
                        $DIC['refinery']
                    ),
                    new Standard\Roles(
                        $DIC['ilObjDataCache']
                    ),
                    new Standard\OrganisationalUnits(),
                    new Standard\Interests(
                        $DIC['ilCtrl']
                    ),
                    new Standard\HelpOffered(
                        $DIC['ilCtrl']
                    ),
                    new Standard\HelpLookedFor(
                        $DIC['ilCtrl']
                    ),
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
                    new Standard\ClientIP(),
                    \ilMapUtil::isActivated() ? new Standard\Location() : null
                ])
            );
        $this['profile.fields.changelisteners'] = fn($c): array =>
            is_readable(CollectListenersObjective::PATH())
                ? include CollectListenersObjective::PATH()
                : [];
        $this[Profile::class] = fn($c): Profile =>
            new ProfileImplementation(
                $DIC['lng'],
                $c[ProfileFieldsConfigurationRepository::class],
                $c[ProfileDataRepository::class]
            );
        $this[EndpointFactory::class] = fn($c): EndpointFactory =>
            new EndpointFactory(
                $c[ProfileFieldsConfigurationRepository::class],
                $c[ProfileDataRepository::class],
                $c[UserSettingsDataRepository::class],
                $DIC['user']->getLoggedInUser(),
                $DIC['http'],
                $DIC['refinery'],
                $DIC['ilCtrl'],
                new DataFactory()
            );
        $this[Search::class] = fn($c): Search => new Search(
            $DIC['ui.factory'],
            $c[EndpointFactory::class]
        );
        $this[NewAccountMailRepository::class] = fn($c): NewAccountMailRepository =>
            new NewAccountMailRepository($DIC['ilDB']);
    }
}
