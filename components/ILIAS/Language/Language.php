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

namespace ILIAS;

use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Setup\InstalledLanguageDatabaseRepository;
use ILIAS\Environment\Configuration\Installation\Directories;

class Language implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = \ILIAS\Language\Language::class;

        // each entry's dependencies are declared above it
        $internal[Language\LanguageFolder::class] = static fn() =>
            new Language\LanguageFolderLegacyProxy();

        $internal[LanguageFileDirectoryManager::class] = static fn() =>
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                ...$seek[LanguageFileDirectory::class]
            );

        $internal[InstalledLanguageDatabaseRepository::class] = static fn() =>
            new InstalledLanguageDatabaseRepository(
                $pull[\ILIAS\Database\Connection::class],
                $internal[LanguageFileDirectoryManager::class],
                $use[Directories::class]->getRoot()
            );

        // Setup hands in its own database via setDbHandler() instead of the Connection
        $internal[\ilSetupLanguage::class] = static fn() =>
            new \ilSetupLanguage(
                "en",
                $internal[LanguageFileDirectoryManager::class]
            );

        $runtime_setup_language = static fn(): \ilSetupLanguage =>
            new \ilSetupLanguage(
                "en",
                $internal[LanguageFileDirectoryManager::class],
                $pull[\ILIAS\Database\Connection::class]
            );

        $internal[InstallLanguage::class] = static fn() =>
            new InstallLanguage(
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $runtime_setup_language(),
                $internal[Language\LanguageFolder::class]
            );

        $internal[UpdateLanguage::class] = static fn() =>
            new UpdateLanguage(
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $runtime_setup_language(),
                $internal[Language\LanguageFolder::class]
            );

        // runtime-only Activities route all legacy access through this provider
        $internal[RepositoryProvider::class] = static fn() =>
            new RepositoryProvider(
                $internal[InstalledLanguageDatabaseRepository::class],
                $pull[\ILIAS\Database\Connection::class],
            );

        $internal[UninstallLanguage::class] = static fn() =>
            new UninstallLanguage(
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        $internal[RemoveLocalLanguageChanges::class] = static fn() =>
            new RemoveLocalLanguageChanges(
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        $internal[AddLanguageEntry::class] = static fn() =>
            new AddLanguageEntry(
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        $internal[SetLanguageDetectionEnabled::class] = static fn() =>
            new SetLanguageDetectionEnabled(
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        $internal[SetLanguageTranslationEnabled::class] = static fn() =>
            new SetLanguageTranslationEnabled(
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        // only proxies to $DIC->language() at call time, hence no constructor arguments
        $internal[Language\LanguageLegacyInitialisationAdapter::class] = static fn() =>
            new Language\LanguageLegacyInitialisationAdapter();

        // Two candidates on purpose, neither line is dead: every $implement assignment is collected,
        // and each bootstrap entry point picks one in its resources/dependency_resolution.php
        // (Setup: ilSetupLanguage, Init: LanguageLegacyInitialisationAdapter).
        $implement[\ILIAS\Language\Language::class] = static fn() =>
            $internal[\ilSetupLanguage::class];

        $implement[\ILIAS\Language\Language::class] = static fn() =>
            $internal[Language\LanguageLegacyInitialisationAdapter::class];

        $provide[LanguageFileDirectoryManager::class] = static fn() =>
            $internal[LanguageFileDirectoryManager::class];

        $contribute[LanguageFileDirectory::class] = static fn() => new MainLanguageFileDirectory();

        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilLanguageSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class],
                $internal[\ilSetupLanguage::class],
                // Setup checks no permissions, hence no RBAC.
                InstallLanguage::forSetup($internal[\ilSetupLanguage::class]),
                UpdateLanguage::forSetup($internal[\ilSetupLanguage::class])
            );

        // Provided under the concrete class because Init.php pulls it, so none may become final
        // (the GUI tests mock them). The $contribute order is the declaration order below and must
        // stay between the Setup\Agent and the UserSettings entry: RenamingDIC numbers the repeated
        // Activity key in call order (see LanguageComponentGraphTest).
        $activities = [
            InstallLanguage::class,
            UpdateLanguage::class,
            UninstallLanguage::class,
            RemoveLocalLanguageChanges::class,
            AddLanguageEntry::class,
            SetLanguageDetectionEnabled::class,
            SetLanguageTranslationEnabled::class,
        ];
        foreach ($activities as $activity) {
            $provide[$activity] = static fn() => $internal[$activity];
            $contribute[\ILIAS\Component\Activities\Activity::class] = static fn() => $internal[$activity];
        }

        $contribute[User\Settings\UserSettings::class] = fn() =>
            new Language\UserSettings\Settings();
    }
}
