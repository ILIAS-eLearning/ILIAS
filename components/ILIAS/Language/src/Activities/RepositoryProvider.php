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

use ILIAS\Administration\Setting;
use ILIAS\Language\Setup\InstalledLanguageRepository;

/**
 * Every legacy and repository access of the runtime language activities. No component offers the
 * settings yet, so they are resolved from the legacy container on first use.
 */
class RepositoryProvider
{
    public function __construct(
        private readonly InstalledLanguageRepository $installed_language_repository,
        private readonly \ilDBInterface $db,
        private ?Setting $settings = null,
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

    /** False if a guard of ilObjLanguage::uninstall() applied and nothing was uninstalled. */
    public function uninstall(int $obj_id): bool
    {
        return $this->languageObject($obj_id)->uninstall() !== '';
    }

    /** False if the language is not installed or its language file is invalid. */
    public function removeLocalChanges(int $obj_id): bool
    {
        return $this->languageObject($obj_id)->removeLocalChanges();
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

    /** Writes the entry into the cached module of lng_modules; a missing or broken cache is left alone. */
    public function updateModuleCache(string $lang_key, string $module, string $identifier, string $value): void
    {
        $set = $this->db->query(
            'SELECT lang_array FROM lng_modules WHERE lang_key = '
            . $this->db->quote($lang_key, 'text') . ' AND module = ' . $this->db->quote($module, 'text')
        );
        $row = $this->db->fetchAssoc($set);
        if ($row === null || !is_string($row['lang_array'] ?? null)) {
            return;
        }

        $entries = unserialize($row['lang_array'], ['allowed_classes' => false]);
        if (!is_array($entries)) {
            return;
        }

        $entries[$identifier] = $value;
        \ilObjLanguage::replaceLangModule($lang_key, $module, $entries);
    }

    public function lookupLogin(int $usr_id): string
    {
        return \ilObjUser::_lookupLogin($usr_id);
    }

    public function setLanguageDetectionEnabled(bool $enabled): void
    {
        $this->settings()->set('lang_detection', $enabled ? '1' : '0');
    }

    public function isPageTranslationEnabled(string $lang_key): bool
    {
        return (bool) $this->settings()->get('lang_translate_' . $lang_key, '0');
    }

    public function setPageTranslationEnabled(string $lang_key, bool $enabled): void
    {
        $this->settings()->set('lang_translate_' . $lang_key, $enabled ? '1' : '0');
    }

    private function languageObject(int $obj_id): \ilObjLanguage
    {
        return new \ilObjLanguage($obj_id);
    }

    private function settings(): Setting
    {
        if (!$this->settings instanceof Setting) {
            global $DIC;

            $this->settings = $DIC->settings();
        }

        return $this->settings;
    }
}
