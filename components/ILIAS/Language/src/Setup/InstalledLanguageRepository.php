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

namespace ILIAS\Language\Setup;

/**
 * Read-only access to the language installation domain: which languages are
 * known to the system (database) and which language files are available or
 * valid (filesystem). This is the "retrieval" half of what used to be
 * bundled into ilSetupLanguage - see its class docblock and
 * docs/development/repository-pattern.md.
 *
 * Write access (installing, flushing, registering languages) lives in
 * LanguageInstallationManager instead.
 */
interface InstalledLanguageRepository
{
    /**
     * Language keys already installed (in the database), including
     * "installed" and "installed_local".
     *
     * @return list<string>
     */
    public function getInstalledLanguages(): array;

    /**
     * Language keys installed as "installed_local" (in the database).
     *
     * @return list<string>
     */
    public function getInstalledLocalLanguages(): array;

    /**
     * All languages registered in object_data, keyed by language key.
     *
     * @return array<string, array{obj_id: int, status: string}>
     */
    public function getAvailableLanguages(): array;

    /**
     * Locally changed language entries for a language key, in a given
     * change-date range.
     *
     * @return array<string, array<string, string>> [module][identifier] => value
     */
    public function getLocalChanges(string $lang_key, string $min_date = "", string $max_date = ""): array;

    /**
     * Every entry currently stored for a language key, regardless of
     * local_change - unlike getLocalChanges(), which only returns entries
     * with a non-null local_change in a given date range (i.e. previously
     * recorded customizations), this also includes plain base/global data.
     * Used to seed a re-seed of lng_data that only reads the customizing
     * directory (see LanguageInstallationManager::insertLanguageForApplyingLocalChanges()),
     * where nothing re-reads the base files to fill in what this doesn't
     * provide.
     *
     * @return array<string, array<string, string>> [module][identifier] => value
     */
    public function getLanguageEntries(string $lang_key): array;

    /**
     * Language keys for which a *.lang.local file exists.
     *
     * @return list<string>
     */
    public function getLocalLanguages(): array;

    /**
     * Language keys for which an installable main *.lang file exists.
     *
     * @return list<string>
     */
    public function getInstallableLanguages(): array;

    /**
     * Local language files whose names contain a requested language key but
     * do not match the expected naming scheme.
     *
     * @param list<string> $language_keys
     * @return list<string>
     */
    public function getInvalidLocalLanguageFiles(array $language_keys): array;

    /**
     * Validates the logical structure of all *.lang files (global,
     * component and, if present, customizing) for a language key.
     */
    public function checkLanguage(string $lang_key): bool;

    /**
     * Validates only the customizing/local language file for a language
     * key. Unlike checkLanguage(), a missing local file is NOT acceptable
     * here - used when a caller explicitly wants to validate a local
     * customization file, e.g. before activating "installed_local" status
     * for an already globally-installed language.
     */
    public function checkLocalLanguageFile(string $lang_key): bool;
}
