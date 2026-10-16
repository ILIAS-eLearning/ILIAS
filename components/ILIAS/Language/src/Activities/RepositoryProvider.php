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
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\ComponentTranslation\MigratedTranslations;
use ILIAS\Language\ComponentTranslation\PluralFormKey;
use ILIAS\Language\Setup\InstallationRoot;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Logging\Logger\LoggerInterface;

/**
 * Every legacy and repository access of the runtime language activities. No component offers the
 * component repository yet, so it is resolved from the legacy container on first use.
 */
class RepositoryProvider
{
    /** @param \Closure(): ?string $client_data_dir_resolver */
    public function __construct(
        private readonly InstalledLanguageRepository $installed_language_repository,
        private readonly \ilDBInterface $db,
        private readonly LanguageSettings $settings,
        private readonly LanguageFileDirectoryManager $language_file_directory_manager,
        private readonly InstallationRoot $installation_root,
        private readonly \Closure $client_data_dir_resolver,
        private readonly LoggerInterface $logger,
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
        try {
            $this->logger->warning($message);
        } catch (\Throwable) {
            // must not replace the exception being reported
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
        // must equal resolveClientDataDir() in replaceLangModule(): re-entrance is detected by lock path only
        $client_data_dir = ($this->client_data_dir_resolver)();

        // read and written under the overlay lock, so a concurrent write is not lost
        return MigratedLanguageFileSync::withOverlayLock(
            $this->language_file_directory_manager,
            $lang_key,
            $module,
            $client_data_dir,
            function () use ($lang_key, $module, $identifier, $value, $remarks, $client_data_dir): bool {
                try {
                    $entries = $this->currentModuleEntries($lang_key, $module, $client_data_dir);
                } catch (\Throwable $t) {
                    MigratedTranslations::reportProblem(sprintf(
                        'The language file of module "%s" for "%s" cannot be read, the new entry is not added to it: %s',
                        $module,
                        $lang_key,
                        $t->getMessage()
                    ));
                    return false;
                }
                if ($entries === null) {
                    // no lng_modules row of a module not maintained in PO files: lng_data holds the entry
                    return true;
                }
                $entries[$this->entryKeyOf($entries, $identifier)] = $value;
                $written = \ilObjLanguage::replaceLangModule($lang_key, $module, $entries);
                if ($remarks === '') {
                    return $written;
                }
                try {
                    MigratedLanguageFileSync::setRemarks(
                        $this->language_file_directory_manager,
                        $lang_key,
                        $module,
                        [$identifier => $remarks],
                        $client_data_dir,
                        $this->installation_root->getPath()
                    );
                } catch (\Throwable) {
                    return false;
                }
                return $written;
            },
            $this->installation_root->getPath()
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
        if (PluralFormKey::parse($identifier) === null) {
            return [];
        }
        $languages = [];
        foreach ($lang_keys as $lang_key) {
            $lang_key = (string) $lang_key;
            $shipped_po = $this->shippedModuleFile($lang_key, $module);
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

    /** @return list<string> "<module>: <identifier>" of the entries that would duplicate it (language.md rule 2) */
    public function findDuplicatesOf(string $module, string $identifier): array
    {
        return self::duplicatesIn(self::lngDataEntriesOf($this->db, $identifier), $module, $identifier);
    }

    /**
     * @param list<string> $modules
     * @param list<string|int> $identifiers
     * @return list<string> identifiers findDuplicatesOf() accepts for at least one module
     */
    public static function filterAddable(\ilDBInterface $db, array $modules, array $identifiers): array
    {
        $addable = [];
        foreach ($identifiers as $identifier) {
            // session topics are array keys, so a numeric topic arrives as int
            $identifier = (string) $identifier;
            $entries = self::lngDataEntriesOf($db, $identifier);
            foreach ($modules as $module) {
                if (self::duplicatesIn($entries, $module, $identifier) === []) {
                    $addable[] = $identifier;
                    break;
                }
            }
        }

        return $addable;
    }

    /**
     * An existing entry of its own (same module, same spelling) keeps an old collision editable.
     * @param list<array{module: string, identifier: string}> $entries
     * @return list<string>
     */
    private static function duplicatesIn(array $entries, string $module, string $identifier): array
    {
        $candidates = self::candidatesOf($identifier);
        $duplicates = [];
        foreach ($entries as $entry) {
            if (strcasecmp($entry['module'], $module) === 0 && in_array($entry['identifier'], $candidates, true)) {
                return [];
            }
            $duplicates[$entry['module'] . ': ' . $entry['identifier']] = true;
        }
        $duplicates = array_keys($duplicates);
        sort($duplicates);

        return $duplicates;
    }

    /** @return list<array{module: string, identifier: string}> */
    private static function lngDataEntriesOf(\ilDBInterface $db, string $identifier): array
    {
        // relies on migrated modules being written to lng_data as well, the ci collation matches other spellings
        $set = $db->query(
            'SELECT module, identifier FROM lng_data WHERE '
            . $db->in('identifier', self::candidatesOf($identifier), false, 'text')
        );
        $entries = [];
        while ($row = $db->fetchAssoc($set)) {
            $entries[] = ['module' => (string) $row['module'], 'identifier' => (string) $row['identifier']];
        }

        return $entries;
    }

    /** @return list<string> $identifier and, for a form key, the identifier of its plural message */
    private static function candidatesOf(string $identifier): array
    {
        return array_values(array_unique([$identifier, PluralFormKey::parse($identifier)[0] ?? $identifier]));
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

    private function shippedModuleFile(string $lang_key, string $module): ?string
    {
        return MigratedLanguageFileSync::findShippedModuleFiles(
            $this->language_file_directory_manager,
            $this->installation_root->getPath(),
            $lang_key
        )[$module] ?? null;
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
     * (shipped state plus overlay, see MigratedLanguageFileSync::loadEditableModuleTranslations()), never
     * its database rows; otherwise the lng_modules row, null without one (the module name is free input).
     *
     * @return array<string, string>|null identifier => value
     * @throws \RuntimeException if the shipped `.po` of a module maintained in PO files cannot be read
     */
    private function currentModuleEntries(string $lang_key, string $module, ?string $client_data_dir): ?array
    {
        $content = MigratedLanguageFileSync::loadEditableModuleTranslations(
            $this->language_file_directory_manager,
            $lang_key,
            $module,
            $client_data_dir,
            $this->installation_root->getPath()
        );
        if ($content !== null) {
            return array_map(static fn(array $entry): string => $entry['value'], $content);
        }

        $set = $this->db->query(
            'SELECT lang_array FROM lng_modules WHERE lang_key = '
            . $this->db->quote($lang_key, 'text') . ' AND module = ' . $this->db->quote($module, 'text')
        );
        $row = $this->db->fetchAssoc($set);
        $entries = is_string($row['lang_array'] ?? null)
            ? unserialize($row['lang_array'], ['allowed_classes' => false])
            : null;

        return is_array($entries) ? $entries : null;
    }

    private function languageObject(int $obj_id): \ilObjLanguage
    {
        return new \ilObjLanguage($obj_id);
    }
}
