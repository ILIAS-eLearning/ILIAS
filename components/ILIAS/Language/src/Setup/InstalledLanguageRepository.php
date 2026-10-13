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
 * Read-only access to the installed languages (database) and the available or valid language files
 * (filesystem). Writing lives in LanguageInstallationManager.
 */
interface InstalledLanguageRepository
{
    /** @return list<string> including "installed_local" */
    public function getInstalledLanguages(): array;

    /** @return list<string> */
    public function getInstalledLocalLanguages(): array;

    /** @return array<string, array{obj_id: int, status: string}> keyed by language key */
    public function getAvailableLanguages(): array;

    /** @return array<string, array<string, string>> [module][identifier] => value, only entries with a local_change in the date range */
    public function getLocalChanges(string $lang_key, string $min_date = "", string $max_date = ""): array;

    /**
     * Unlike getLocalChanges(), every entry regardless of local_change: seeds a re-seed that only
     * reads the customizing directory, see LanguageInstallationManager::insertLanguageForApplyingLocalChanges().
     *
     * @return array<string, array<string, string>> [module][identifier] => value
     */
    public function getLanguageEntries(string $lang_key): array;

    /** @return list<string> keys with a *.lang.local file */
    public function getLocalLanguages(): array;

    /** @return list<string> keys with an installable main *.lang file */
    public function getInstallableLanguages(): array;

    /**
     * Local language files whose names contain a requested key but do not match the naming scheme.
     *
     * @param list<string> $language_keys
     * @return list<string>
     */
    public function getInvalidLocalLanguageFiles(array $language_keys): array;

    /** Validates the global, component and customizing *.lang files of $lang_key. */
    public function checkLanguage(string $lang_key): bool;

    /** Unlike checkLanguage(), a missing local file is NOT acceptable here. */
    public function checkLocalLanguageFile(string $lang_key): bool;
}
