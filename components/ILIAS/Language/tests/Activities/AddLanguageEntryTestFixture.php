<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Language;
use ILIAS\Language\Setup\InstalledLanguageRepository;

trait AddLanguageEntryTestFixture
{
    /**
     * @param array<string, array> $calls
     */
    protected function spyReplaceLangEntry(array &$calls): \Closure
    {
        return static function (
            string $module,
            string $identifier,
            string $lang_key,
            string $value,
            string $local_change,
            string $remarks
        ) use (&$calls): bool {
            $calls['replace'][] = [$module, $identifier, $lang_key, $value, $local_change, $remarks];
            return true;
        };
    }

    /**
     * @param array<string, array> $calls
     */
    protected function spyUpdateModuleCache(array &$calls): \Closure
    {
        return static function (
            string $lang_key,
            string $module,
            string $identifier,
            string $value,
            string $remarks = ''
        ) use (&$calls): void {
            $calls['cache'][] = [$lang_key, $module, $identifier, $value];
        };
    }

    /**
     * The provider answers from the closures; a closure of update_module_cache() returning null (void) counts as
     * written, as does the default.
     *
     * @param list<string> $installed_languages
     * @param \Closure|null $languages_without_plural_form (string $module, string $identifier, array $lang_keys): list<string>
     */
    protected function createActivity(
        array $installed_languages,
        ?\ilRbacSystem $rbac = null,
        ?Language $language = null,
        ?InstalledLanguageRepository $installed_language_repository = null,
        int $language_folder_ref_id = 0,
        ?\Closure $replace_lang_entry = null,
        ?\Closure $update_module_cache = null,
        ?\Closure $user_login = null,
        ?\Closure $languages_without_plural_form = null,
    ): AddLanguageEntry {
        // no-op defaults; tests proving a closure is never called pass a throwing one
        $replace_lang_entry ??= static fn(
            string $module,
            string $identifier,
            string $lang_key,
            string $value,
            string $local_change,
            string $remarks
        ): bool => true;
        $update_module_cache ??= static fn(
            string $lang_key,
            string $module,
            string $identifier,
            string $value,
            string $remarks = ''
        ): bool => true;
        $user_login ??= static fn(int $usr_id): string => 'default-login';
        $languages_without_plural_form ??= static fn(string $module, string $identifier, array $lang_keys): array => [];
        $installed_language_repository ??= new FakeInstalledLanguageRepository(static fn(): array => $installed_languages);

        $repository = $this->createStub(RepositoryProvider::class);
        $repository->method('getInstalledLanguages')->willReturnCallback(
            static fn(): array => $installed_language_repository->getInstalledLanguages()
        );
        $repository->method('replaceLangEntry')->willReturnCallback($replace_lang_entry);
        $repository->method('updateModuleCache')->willReturnCallback(
            static fn(
                string $lang_key,
                string $module,
                string $identifier,
                string $value,
                string $remarks = ''
            ): bool => $update_module_cache($lang_key, $module, $identifier, $value, $remarks) ?? true
        );
        $repository->method('lookupLogin')->willReturnCallback($user_login);
        $repository->method('languagesWithoutPluralForm')->willReturnCallback($languages_without_plural_form);

        return new AddLanguageEntry(
            $language ?? $this->createStub(Language::class),
            $this->rbacOf($rbac ?? $this->createStub(\ilRbacSystem::class)),
            $this->languageFolder($language_folder_ref_id),
            $repository,
        );
    }
}
