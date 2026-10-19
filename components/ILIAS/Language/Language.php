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

        // --- $internal: wiring local to this component -----------------
        // Ordered so each entry's dependencies are declared above it.

        $internal[Language\LanguageFolder::class] = static fn() =>
            new Language\LanguageFolderLegacyProxy();

        $internal[LanguageFileDirectoryManager::class] = static fn() =>
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                ...$seek[LanguageFileDirectory::class]
            );

        // Read access to the language installation domain, extracted from
        // ilSetupLanguage per docs/development/repository-pattern.md - see that
        // class' docblock.
        $internal[InstalledLanguageDatabaseRepository::class] = static fn() =>
            new InstalledLanguageDatabaseRepository(
                $pull[\ILIAS\Database\Connection::class],
                $internal[LanguageFileDirectoryManager::class],
                $use[Directories::class]->getRoot()
            );

        // Setup instance: Setup hands in its own database via setDbHandler()
        // instead of the Connection (see components/ILIAS/Database/ROADMAP.md).
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
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $runtime_setup_language(),
                $internal[Language\LanguageFolder::class]
            );

        $internal[UpdateLanguage::class] = static fn() =>
            new UpdateLanguage(
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $runtime_setup_language(),
                $internal[Language\LanguageFolder::class]
            );

        // runtime-only Activities (no Setup counterpart): all their legacy access goes through this provider
        $internal[RepositoryProvider::class] = static fn() =>
            new RepositoryProvider(
                $internal[InstalledLanguageDatabaseRepository::class],
                $pull[\ILIAS\Database\Connection::class],
            );

        $internal[UninstallLanguage::class] = static fn() =>
            new UninstallLanguage(
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        $internal[RemoveLocalLanguageChanges::class] = static fn() =>
            new RemoveLocalLanguageChanges(
                $use[\ILIAS\Language\Language::class],
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
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        $internal[SetLanguageTranslationEnabled::class] = static fn() =>
            new SetLanguageTranslationEnabled(
                $use[\ILIAS\Language\Language::class],
                $use[\ILIAS\AccessControl\PublicInterface\RBAC::class],
                $internal[Language\LanguageFolder::class],
                $internal[RepositoryProvider::class],
            );

        // LanguageLegacyInitialisationAdapter has no constructor of its own,
        // so it never needs the LanguageFileDirectoryManager argument -
        // it purely proxies to $DIC->language() at call time. This slot used
        // to be misleadingly named $internal[\ilLanguage::class] even though
        // it never held an \ilLanguage instance.
        $internal[Language\LanguageLegacyInitialisationAdapter::class] = static fn() =>
            new Language\LanguageLegacyInitialisationAdapter();

        // --- $implement --------------------------------------------------

        // This component registers TWO candidate implementations for
        // \ILIAS\Language\Language. Both assignments below are intentional -
        // ILIAS\Component\Dependencies\Reader::cacheImplement() collects every
        // $implement[...] assignment into a list rather than overwriting a
        // plain array key, and the generated RenamingDIC keeps each candidate
        // under its own offset, so neither line is dead code.
        // Consumers that $use[\ILIAS\Language\Language::class] (e.g. UI.php,
        // Setup.php, Refinery.php) are disambiguated per bootstrap entry point:
        //   - components/ILIAS/Setup/resources/dependency_resolution.php -> ilSetupLanguage
        //   - components/ILIAS/Init/resources/dependency_resolution.php  -> LanguageLegacyInitialisationAdapter
        // See docs/development/components-and-directories.md and cli/build_bootstrap.php.
        $implement[\ILIAS\Language\Language::class] = static fn() =>
            $internal[\ilSetupLanguage::class];

        $implement[\ILIAS\Language\Language::class] = static fn() =>
            $internal[Language\LanguageLegacyInitialisationAdapter::class];

        // --- $provide: services made available to the rest of the system -

        // Make the resolved language services available outside this component.
        $provide[LanguageFileDirectoryManager::class] = static fn() =>
            $internal[LanguageFileDirectoryManager::class];

        // --- $contribute: contributions to other components' collection
        //     points. Relative order preserved from before this file's
        //     reorganisation, since components may in general add multiple
        //     contributions to the same collection point sequentially (see
        //     docs/development/components-and-directories.md, "Contribute to
        //     Service or Functionality") - which the seven
        //     \ILIAS\Component\Activities\Activity::class entries below
        //     actually do, one per Activity this component offers.

        $contribute[LanguageFileDirectory::class] = static fn() => new MainLanguageFileDirectory();

        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilLanguageSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class],
                $internal[\ilSetupLanguage::class],
                // Setup checks no permissions, hence no RBAC.
                InstallLanguage::forSetup($internal[\ilSetupLanguage::class]),
                UpdateLanguage::forSetup($internal[\ilSetupLanguage::class])
            );

        // The seven Activities this component offers are both provided under
        // their own concrete class (so components/ILIAS/Init/Init.php can
        // pull each one specifically - AllModernComponents.php then
        // re-exposes that same resolved instance under the matching legacy
        // $DIC[<Activity>::class] key, which is what every GUI class in
        // this component reads) and contributed generically to
        // \ILIAS\Component\Activities\Activity::class (so the
        // cross-component Repository can discover them too) - see
        // docs/development/components-and-directories.md, "Contribute to
        // Service or Functionality". Because Init.php pulls these concrete
        // classes (not an interface), none of the seven may become final - both
        // ilObjLanguageFolderGUITest and ilObjLanguageExtGUITest build a
        // PHPUnit mock of several of them directly (createMock(InstallLanguage::class)
        // and siblings), which requires PHPUnit to generate a subclass at
        // runtime - impossible for a final class.
        //
        // Kept as one combined loop rather than seven repeated
        // $provide[...]/$contribute[...] pairs. Order matters for the
        // $contribute side only (RenamingDIC assigns a shared, container-wide
        // "_<counter>" suffix in call order to disambiguate the repeated
        // \ILIAS\Component\Activities\Activity::class key - see
        // LanguageComponentGraphTest::testContributeEntriesResolveToExpectedConcreteClassesInPreservedOrder());
        // this list's order must therefore match $activities' declaration
        // order exactly, and this loop must stay exactly here: after the two
        // preceding $contribute entries (LanguageFileDirectory, Setup\Agent)
        // and before the following one (UserSettings).
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
