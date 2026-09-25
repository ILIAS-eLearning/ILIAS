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
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\InstalledLanguageDatabaseRepository;
use ILIAS\Language\Setup\LanguageInstallationManager;

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

        // Shared by every $internal service below that needs the Setup/
        // installation database - deduplicated here instead of being
        // reconstructed identically in each closure.
        $resolve_db = static fn(): \ilDBInterface => $GLOBALS['ilDB'] ?? $GLOBALS['DIC']->database();
        $ilias_root = (string) realpath(__DIR__ . '/../../../');

        // Shared by every Activity below that needs a "write" RBAC check on
        // the language folder ref_id (all seven), or the system settings
        // service (SetLanguageDetectionEnabled/SetLanguageTranslationEnabled) -
        // deduplicated here for the same reason as $resolve_db/$ilias_root
        // above. These stay Closures, not resolved values: init() runs at
        // bootstrap-build time, without a database or a fully booted $DIC.
        $rbac_system = static fn(): \ilRbacSystem => $GLOBALS['DIC']->rbac()->system();
        $lang_folder_ref_id = static fn(): int => \ilObjLanguageAccess::_lookupLangFolderRefId();
        $settings = static fn(): \ILIAS\Administration\Setting => $GLOBALS['DIC']->settings();

        // --- $internal: wiring local to this component -----------------
        // Ordered so each entry's dependencies are declared above it.

        $internal[LanguageFileDirectoryManager::class] = static fn() =>
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                ...$seek[LanguageFileDirectory::class]
            );

        // Read (InstalledLanguageRepository) and write (LanguageInstallationManager)
        // access to the language installation domain, extracted from
        // ilSetupLanguage per docs/development/repository-pattern.md - see that
        // class' docblock. ilSetupLanguage itself keeps delegating to both and
        // remains the \ILIAS\Language\Language implementation used during Setup
        // (see $implement[...] below); these two are for consumers that only
        // need install/retrieval behaviour, not txt() - see $provide[...] below
        // and components/ILIAS/Language/README.md.
        $internal[InstalledLanguageDatabaseRepository::class] = static fn() =>
            new InstalledLanguageDatabaseRepository(
                $resolve_db,
                $internal[LanguageFileDirectoryManager::class],
                $ilias_root
            );

        $internal[LanguageInstallationManager::class] = static fn() =>
            new LanguageInstallationManager(
                $resolve_db,
                $internal[LanguageFileDirectoryManager::class],
                $ilias_root,
                $internal[InstalledLanguageDatabaseRepository::class]
            );

        $internal[\ilSetupLanguage::class] = static fn() =>
            new \ilSetupLanguage(
                "en",
                $internal[LanguageFileDirectoryManager::class]
            );

        $internal[InstallLanguage::class] = static fn() =>
            new InstallLanguage(
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $rbac_system,
                // The Activity needs no database of its own: every database
                // access in perform() goes through ilSetupLanguage, which
                // resolves it itself.
                $internal[\ilSetupLanguage::class],
                $lang_folder_ref_id
            );

        $internal[UpdateLanguage::class] = static fn() =>
            new UpdateLanguage(
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $rbac_system,
                // Same reasoning as InstallLanguage above: no database of
                // its own, ilSetupLanguage resolves it.
                $internal[\ilSetupLanguage::class],
                $lang_folder_ref_id
            );

        // Unlike InstallLanguage/UpdateLanguage, UninstallLanguage needs no
        // ilSetupLanguage - uninstalling a language is a runtime-only action with no
        // equivalent step during Setup - and so no forSetup() factory either; its two legacy
        // collaborators (enumerating "lng" objects, constructing an ilObjLanguage by id) are
        // left at their defaults, which resolve the exact same legacy calls the extracted GUI
        // code used directly before.
        $internal[UninstallLanguage::class] = static fn() =>
            new UninstallLanguage(
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $rbac_system,
                $lang_folder_ref_id
            );

        // Same reasoning as UninstallLanguage above: no Setup counterpart, no
        // ilSetupLanguage, no forSetup() factory; its two legacy
        // collaborators (enumerating "lng" objects, constructing an
        // ilObjLanguage by id) are left at their defaults, which resolve the
        // exact same legacy calls the extracted GUI code used directly
        // before.
        $internal[RemoveLocalLanguageChanges::class] = static fn() =>
            new RemoveLocalLanguageChanges(
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $rbac_system,
                $lang_folder_ref_id
            );

        // Same reasoning again, minus the Setup/forSetup() bridge, which does not apply here
        // either - adding a single language entry is a runtime-only action with no equivalent
        // step during Setup. Unlike UninstallLanguage/RemoveLocalLanguageChanges, this needs no
        // "lng" object enumeration or ilObjLanguage-by-id closures - it reads the set of
        // installed languages from InstalledLanguageDatabaseRepository (already built above for
        // other consumers) and writes single entries via the two legacy closures defaulted
        // inside AddLanguageEntry itself; the second of those two (updateModuleCache()) needs a
        // database connection, supplied here via the same $resolve_db closure every other
        // database consumer in this component shares, rather than AddLanguageEntry reaching
        // into $GLOBALS['DIC'] directly.
        $internal[AddLanguageEntry::class] = static fn() =>
            new AddLanguageEntry(
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $rbac_system,
                $internal[InstalledLanguageDatabaseRepository::class],
                // $db is a mandatory (non-nullable) collaborator - AddLanguageEntry needs no
                // database of its own beyond what updateModuleCache()'s default resolves via
                // this same $resolve_db closure, already shared by
                // InstalledLanguageDatabaseRepository/LanguageInstallationManager.
                $resolve_db,
                language_folder_ref_id: $lang_folder_ref_id,
                // replace_lang_entry/update_module_cache/user_login: left at
                // their defaults (see AddLanguageEntry's own constructor
                // docblock).
            );

        // Same reasoning again, minus the Setup/forSetup() bridge, which does not apply here
        // either - toggling a system setting is a runtime-only action with no equivalent step
        // during Setup. Unlike every other Activity above, this needs no "lng" object
        // enumeration, no ilObjLanguage-by-id closure and no installed-language repository - it
        // only ever reads/writes the single "lang_detection" system setting, via the same
        // $GLOBALS['DIC']->settings() call the extracted GUI code used directly (as
        // $this->settings).
        $internal[SetLanguageDetectionEnabled::class] = static fn() =>
            new SetLanguageDetectionEnabled(
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $rbac_system,
                $settings,
                $lang_folder_ref_id
            );

        // Same reasoning again, minus the Setup/forSetup() bridge, which does not apply here
        // either - toggling a per-language setting is a runtime-only action with no equivalent
        // step during Setup. Like SetLanguageDetectionEnabled, this needs no "lng" object
        // enumeration and no ilObjLanguage-by-id closure - it only ever reads/writes a single
        // "lang_translate_<key>" system setting, via the same $GLOBALS['DIC']->settings() call
        // the extracted GUI code used directly (as $DIC->settings() inside
        // saveSettingsObject()). Unlike SetLanguageDetectionEnabled, it now also needs the
        // installed-language repository (already built above for AddLanguageEntry) to validate
        // that the given language_key is actually installed.
        $internal[SetLanguageTranslationEnabled::class] = static fn() =>
            new SetLanguageTranslationEnabled(
                $pull[\ILIAS\Refinery\Factory::class],
                $use[\ILIAS\Language\Language::class],
                $rbac_system,
                $settings,
                $internal[InstalledLanguageDatabaseRepository::class],
                language_folder_ref_id: $lang_folder_ref_id,
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

        $provide[InstalledLanguageRepository::class] = static fn() =>
            $internal[InstalledLanguageDatabaseRepository::class];

        $provide[LanguageInstallationManager::class] = static fn() =>
            $internal[LanguageInstallationManager::class];

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
                $internal[InstallLanguage::class],
                $internal[UpdateLanguage::class],
                $internal[InstalledLanguageDatabaseRepository::class]
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
        // Service or Functionality". InstallLanguage/UpdateLanguage are
        // additionally handed to Setup Objectives directly, via
        // $contribute[\ILIAS\Setup\Agent::class] above, or via their own
        // forSetup() factory for callers not wired through the component
        // graph; because Init.php pulls these concrete classes (not an
        // interface), none of the seven may become final - both
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
