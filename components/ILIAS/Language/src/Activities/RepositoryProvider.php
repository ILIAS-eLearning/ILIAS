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

namespace ILIAS\Language\Activities;

use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\ComponentTranslation\PluralFormKey;
use ILIAS\Language\Setup\InstalledLanguageRepository;

/**
 * Every legacy and repository access of the runtime language activities. No component offers the
 * logger and the component repository yet, so they are resolved from the legacy container on first use.
 * Without a LanguageFileDirectoryManager no module is treated as maintained in PO files.
 */
class RepositoryProvider
{
    public function __construct(
        private readonly InstalledLanguageRepository $installed_language_repository,
        private readonly \ilDBInterface $db,
        private readonly LanguageSettings $settings,
        private readonly ?LanguageFileDirectoryManager $language_file_directory_manager = null,
    ) {
    }

    /** @return list<string> */
    public function getInstalledLanguages(): array
    {
        return $this->installed_language_repository->getInstalledLanguages();
    }

    /** @return list<array{obj_id: int, title: string}> */
    public function listLanguageObjects(): array
    {
        $objects = [];
        foreach (\ilObject::_getObjectsByType('lng') as $object) {
            $objects[] = ['obj_id' => (int) $object['obj_id'], 'title' => (string) $object['title']];
        }

        return $objects;
    }

    public function isSystemLanguage(int $obj_id): bool
    {
        return $this->languageObject($obj_id)->isSystemLanguage();
    }

    /** Compares against the session language of the current request, not of a given user. */
    public function isUserLanguage(int $obj_id): bool
    {
        return $this->languageObject($obj_id)->isUserLanguage();
    }

    public function isInstalled(int $obj_id): bool
    {
        return $this->languageObject($obj_id)->isInstalled();
    }

    /**
     * The overlay state is only known to the object that uninstalled, hence returned here.
     *
     * @return list<string>|null null if a guard of ilObjLanguage::uninstall() applied and nothing was
     *         uninstalled, otherwise the modules whose PO/MO overlay could not be written
     */
    public function uninstall(int $obj_id): ?array
    {
        $language = $this->languageObject($obj_id);
        if ($language->uninstall() === '') {
            return null;
        }

        return array_values($language->getModulesWithUnwrittenOverlay());
    }

    /**
     * @return list<string>|null null if the language is not installed or its language file is invalid,
     *         otherwise the modules whose PO/MO overlay could not be written
     */
    public function removeLocalChanges(int $obj_id): ?array
    {
        $language = $this->languageObject($obj_id);
        if (!$language->removeLocalChanges()) {
            return null;
        }

        return array_values($language->getModulesWithUnwrittenOverlay());
    }

    /**
     * Re-applies the language files of the active plugins; a no-op without the component repository (Setup).
     *
     * @param list<string> $lang_keys
     */
    public function refreshPlugins(array $lang_keys): void
    {
        global $DIC;

        if ($DIC instanceof \ILIAS\DI\Container && $DIC->offsetExists('component.repository')) {
            \ilObjLanguage::refreshPlugins($lang_keys);
        }
    }

    public function logWarning(string $message): void
    {
        global $DIC;

        try {
            $DIC->logger()->forComponent('lang')->warning($message);
        } catch (\Throwable) {
            error_log($message);
        }
    }

    public function replaceLangEntry(
        string $module,
        string $identifier,
        string $lang_key,
        string $value,
        string $local_change,
        string $remarks
    ): bool {
        return \ilObjLanguage::replaceLangEntry($module, $identifier, $lang_key, $value, $local_change, $remarks);
    }

    /**
     * Writes the entry into the module content ilLanguage serves, and $remarks into the overlay.
     *
     * @return bool false if the entry reached lng_data, but the PO/MO overlay could not be written
     */
    public function updateModuleCache(
        string $lang_key,
        string $module,
        string $identifier,
        string $value,
        string $remarks = ''
    ): bool {
        $client_data_dir = MigratedLanguageFilePaths::resolveClientDataDir(ILIAS_ABSOLUTE_PATH);

        // read and written under the overlay lock, so a concurrent write is not lost
        return MigratedLanguageFileSync::withOverlayLock(
            $this->language_file_directory_manager,
            $lang_key,
            $module,
            $client_data_dir,
            function () use ($lang_key, $module, $identifier, $value, $remarks, $client_data_dir): bool {
                $entries = $this->currentModuleEntries($lang_key, $module);
                if ($entries === null) {
                    // no lng_modules row of a module not maintained in PO files: lng_data holds the entry
                    return true;
                }
                $entries[$this->entryKeyOf($entries, $identifier)] = $value;
                $written = \ilObjLanguage::replaceLangModule($lang_key, $module, $entries);
                if ($remarks === '' || $this->language_file_directory_manager === null) {
                    return $written;
                }
                try {
                    MigratedLanguageFileSync::setRemarks(
                        $this->language_file_directory_manager,
                        $lang_key,
                        $module,
                        [$identifier => $remarks],
                        $client_data_dir,
                        ILIAS_ABSOLUTE_PATH
                    );
                } catch (\Throwable) {
                    return false;
                }
                return $written;
            },
            ILIAS_ABSOLUTE_PATH
        );
    }

    /**
     * The languages whose shipped `.po` has a plural message for $identifier that lacks this form
     * (e.g. `poll_population [2]`); a `.po` that cannot be read is skipped.
     *
     * @param list<string|int> $lang_keys
     * @return list<string>
     */
    public function languagesWithoutPluralForm(string $module, string $identifier, array $lang_keys): array
    {
        if (PluralFormKey::parse($identifier) === null || $this->language_file_directory_manager === null) {
            return [];
        }
        $languages = [];
        foreach ($lang_keys as $lang_key) {
            $lang_key = (string) $lang_key;
            $shipped_po = MigratedLanguageFileSync::findShippedModuleFiles(
                $this->language_file_directory_manager,
                ILIAS_ABSOLUTE_PATH,
                $lang_key
            )[$module] ?? null;
            if ($shipped_po === null) {
                continue;
            }
            try {
                $shipped = MigratedLanguageFileSync::loadShippedModuleEntries($shipped_po, $module);
            } catch (\Throwable) {
                continue;
            }
            if (MigratedLanguageFileSync::pluralMessageOf($identifier, $shipped) !== null && !array_key_exists($identifier, $shipped)) {
                $languages[] = $lang_key;
            }
        }

        return $languages;
    }

    public function lookupLogin(int $usr_id): string
    {
        return \ilObjUser::_lookupLogin($usr_id);
    }

    public function setLanguageDetectionEnabled(bool $enabled): void
    {
        $this->settings->setLanguageDetectionEnabled($enabled);
    }

    public function isPageTranslationEnabled(string $lang_key): bool
    {
        return $this->settings->isPageTranslationEnabled($lang_key);
    }

    public function setPageTranslationEnabled(string $lang_key, bool $enabled): void
    {
        $this->settings->setPageTranslationEnabled($lang_key, $enabled);
    }

    /**
     * The default form's key for a plural message of a module maintained in PO files (the one the
     * database row stands for), otherwise $identifier itself.
     *
     * @param array<string|int, string> $entries
     */
    private function entryKeyOf(array $entries, string $identifier): string
    {
        return MigratedLanguageFileSync::defaultFormKeyOf($identifier, $entries) ?? $identifier;
    }

    /**
     * The content the new entry is added to: for a module maintained in PO files what ilLanguage serves
     * (shipped state plus overlay), otherwise or if unreadable the lng_modules row. Without a row only a
     * PO module is rebuilt from lng_data, so its entry never hides behind the overlay; any other module
     * gives null, since the module name is free input and no row may be created for an arbitrary one.
     *
     * @return array<string, string>|null identifier => value
     */
    private function currentModuleEntries(string $lang_key, string $module): ?array
    {
        $is_migrated = $this->language_file_directory_manager !== null
            && isset(MigratedLanguageFileSync::findShippedModuleFiles(
                $this->language_file_directory_manager,
                ILIAS_ABSOLUTE_PATH,
                $lang_key
            )[$module]);
        if ($is_migrated) {
            try {
                $overlay = MigratedLanguageFileSync::loadModuleTranslations(
                    $this->language_file_directory_manager,
                    $lang_key,
                    $module,
                    MigratedLanguageFilePaths::resolveClientDataDir(ILIAS_ABSOLUTE_PATH),
                    ILIAS_ABSOLUTE_PATH
                );
            } catch (\Throwable) {
                // an unreadable overlay is rebuilt by the sync, the database content is the base then
                $overlay = null;
            }
            if ($overlay !== null) {
                return array_map(static fn(array $entry): string => $entry['value'], $overlay);
            }
        }

        $set = $this->db->query(
            'SELECT lang_array FROM lng_modules WHERE lang_key = '
            . $this->db->quote($lang_key, 'text') . ' AND module = ' . $this->db->quote($module, 'text')
        );
        $row = $this->db->fetchAssoc($set);
        $entries = is_string($row['lang_array'] ?? null)
            ? unserialize($row['lang_array'], ['allowed_classes' => false])
            : null;
        if (is_array($entries)) {
            return $entries;
        }
        if (!$is_migrated) {
            return null;
        }

        $entries = [];
        $set = $this->db->query(
            'SELECT identifier, value FROM lng_data WHERE lang_key = '
            . $this->db->quote($lang_key, 'text') . ' AND module = ' . $this->db->quote($module, 'text')
        );
        while ($row = $this->db->fetchAssoc($set)) {
            $entries[(string) $row['identifier']] = (string) $row['value'];
        }

        return $entries;
    }

    private function languageObject(int $obj_id): \ilObjLanguage
    {
        return new \ilObjLanguage($obj_id);
    }
}
