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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\ComponentTranslation\PlainLogText;
use ILIAS\Language\ComponentTranslation\PluralForms;

/**
 * @author   Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilPluginLanguage
{
    protected ilPluginInfo $plugin_info;

    public function __construct(ilPluginInfo $plugin_info)
    {
        $this->plugin_info = $plugin_info;
    }

    protected function getLanguageDirectory(): string
    {
        return $this->plugin_info->getPath() . "/lang";
    }

    /**
     * Get array of all language files in the plugin
     *
     * A language may be shipped as `ilias_<lang>.po` instead of `.lang` (see readPoFile()); if both exist, the `.po` is used.
     *
     * @return array of [key => "en" (e.g.), file => ...]
     */
    public function getAvailableLangFiles(): array
    {
        $directory = $this->getLanguageDirectory();
        if (!@is_dir($directory)) {
            return [];
        }

        $lang_files = [];
        $po_files = [];

        $dir = opendir($directory);
        while ($file = readdir($dir)) {
            if ($file === "." || $file === "..") {
                continue;
            }

            // directories
            if (!@is_file($directory . "/" . $file)) {
                continue;
            }
            if (strpos($file, "ilias_") === 0 && substr($file, strlen($file) - 5) === ".lang") {
                $lang_files[substr($file, 6, 2)] = [
                    "key" => substr($file, 6, 2),
                    "file" => $file
                ];
            } elseif (preg_match('/\Ailias_([a-z]{2})\.po\z/', $file, $matches) === 1) {
                $po_files[$matches[1]] = [
                    "key" => $matches[1],
                    "file" => $file
                ];
            }
        }
        closedir($dir);

        return array_values(array_replace($lang_files, $po_files));
    }

    public function hasAvailableLangFiles(): bool
    {
        return count($this->getAvailableLangFiles()) > 0;
    }

    public function getPrefix(): string
    {
        $plugin = $this->plugin_info;
        $component = $plugin->getComponent();
        $slot = $plugin->getPluginSlot();

        return $component->getId() . "_" . $slot->getId() . "_" . $plugin->getId();
    }

    /**
     * Update all or selected languages
     *
     * @var array|null $a_lang_keys keys of languages to be updated (null for all)
     */
    public function updateLanguages(?array $a_lang_keys = null): void
    {
        // get the keys of all installed languages if keys are not provided
        if (!isset($a_lang_keys)) {
            $a_lang_keys = ilObjLanguage::getLangKeysOfInstalledLanguages();
        }

        $langs = $this->getAvailableLangFiles();

        $prefix = $this->getPrefix();

        foreach ($langs as $lang) {
            // check if the language should be updated, otherwise skip it
            if (!in_array($lang['key'], $a_lang_keys, true)) {
                continue;
            }

            $file = $this->getLanguageDirectory() . "/" . $lang["file"];
            $values = str_ends_with($lang["file"], ".po") ? $this->readPoFile($file) : $this->readLangFile($file);
            if ($values === null) {
                // a broken .po: this language of the plugin is left as it is (logged)
                continue;
            }
            $lang_array = [];

            // get locally changed variables of the module (these should be kept)
            $local_changes = ilObjLanguage::_getLocalChangesByModule($lang['key'], $prefix);

            foreach ($values as $key => $value) {
                $identifier = $prefix . "_" . $key;

                if (isset($local_changes[$identifier])) {
                    $lang_array[$identifier] = $local_changes[$identifier];
                } else {
                    $lang_array[$identifier] = $value;
                    ilObjLanguage::replaceLangEntry($prefix, $identifier, $lang["key"], $value);
                }
            }

            // $refresh_original_from_shipped: the content is the plugin's own shipped file, like a core component update.
            // No overlay is written yet: a plugin cannot contribute a LanguageFileDirectory (the component graph is
            // built from components/ only), a plugin .po goes into the database only.
            ilObjLanguage::replaceLangModule($lang["key"], $prefix, $lang_array, true);
        }
    }

    /**
     * Key (without the plugin prefix) => value. Disallowed markup is only logged (see logShippedViolations()), unlike
     * for a `.po` (see readPoFile()), whose values are cleaned.
     *
     * @return array<string, string>
     */
    private function readLangFile(string $file): array
    {
        $values = [];
        $txt = file($file);
        if (is_array($txt)) {
            foreach ($txt as $row) {
                if ($row[0] !== "#" && strpos($row, "#:#") > 0) {
                    $a = explode("#:#", trim($row));
                    $values[trim($a[0])] = trim($a[1]);
                }
            }
        }

        $this->logShippedViolations($file, $values);

        return $values;
    }

    /**
     * Logs every value with disallowed markup, changes nothing.
     *
     * @param array<string, string> $values key => value
     */
    private function logShippedViolations(string $file, array $values): void
    {
        foreach ((new TranslationMarkupPolicy())->findInvalidValues($values) as $key => $violations) {
            self::logWarning(sprintf(
                'Markup not allowed in a language value, %s (key "%s"), taken over unchanged: %s',
                $file,
                $key,
                implode(', ', $violations)
            ));
        }
    }

    /**
     * Removes disallowed markup and logs what was removed.
     *
     * @param array<string, string> $values key => value
     * @return array<string, string>
     */
    private function cleanShippedValues(string $file, array $values): array
    {
        $policy = new TranslationMarkupPolicy();
        foreach ($policy->findInvalidValues($values) as $key => $violations) {
            $values[$key] = $policy->sanitize($values[$key]);
            self::logWarning(sprintf(
                'Markup not allowed in a language value, %s (key "%s"): removed %s',
                $file,
                $key,
                implode(', ', $violations)
            ));
        }

        return $values;
    }

    /**
     * Key (msgid, without the plugin prefix) => value, `null` if the file cannot be read (logged, the update goes on
     * with the other languages).
     *
     * Only entries without msgctxt are read: the file belongs to the plugin's module, whose name (the prefix) must not
     * appear in it, as it changes when the plugin becomes a component. Entries with a msgctxt, even an empty one, are
     * ignored and logged. Disallowed markup is cleaned (and logged) like the build does for a component's shipped
     * `.po`. Extracted comments are not stored. Fuzzy entries are read, untranslated ones not. A plural message is read
     * with its default form (see PluralForms) and logged: plugins are served from the database, which holds one
     * value per key.
     *
     * @return array<string, string>|null
     */
    private function readPoFile(string $file): ?array
    {
        try {
            $catalog = TranslationCatalog::fromPoFile($file);
        } catch (\RuntimeException $e) {
            self::logWarning(sprintf(
                'The plugin language file "%s" cannot be read - this language of the plugin is not updated: %s',
                $file,
                $e->getMessage()
            ));
            return null;
        }

        $values = [];
        $ignored = [];
        $plurals = [];
        $plural_forms = null;
        foreach ($catalog->getEntries() as $entry) {
            if ($entry->getContext() !== null) {
                $ignored[] = $entry->getId();
                continue;
            }
            $value = $entry->getTranslation();
            if ($entry->isPlural()) {
                $plural_forms ??= PluralForms::fromHeaderOrGermanic(
                    $catalog->getHeader('Plural-Forms'),
                    static fn(string $message) => self::logWarning(sprintf('Plugin language file "%s": %s', $file, $message))
                );
                $value = $plural_forms->defaultValueOf($entry->getPluralTranslations());
                $plurals[] = $entry->getId();
            }
            if ($value !== '') {
                $values[$entry->getId()] = $value;
            }
        }
        if ($plurals !== []) {
            self::logWarning(sprintf(
                'Plural messages in the plugin language file "%s" are stored with their default form only (plugins are served from the database): %s',
                $file,
                implode(', ', $plurals)
            ));
        }
        $values = $this->cleanShippedValues($file, $values);
        if ($ignored !== []) {
            self::logWarning(sprintf(
                'Entries with a msgctxt in the plugin language file "%s" are ignored (a plugin .po has none): %s',
                $file,
                implode(', ', $ignored)
            ));
        }

        return $values;
    }

    /**
     * Falls back to the PHP error log (e.g. in Setup): a plugin language update must not fail because it cannot log.
     */
    private static function logWarning(string $message): void
    {
        global $DIC;
        $message = PlainLogText::of($message);
        try {
            $DIC->logger()->forComponent('lang')->warning($message);
        } catch (\Throwable) {
            error_log($message);
        }
    }

    public function uninstall(): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        // remove all language entries (see ilObjLanguage)
        // see updateLanguages
        $prefix = $this->getPrefix();
        if ($prefix) {
            $ilDB->manipulate(
                "DELETE FROM lng_data" .
                " WHERE module = " . $ilDB->quote($prefix, "text")
            );
            $ilDB->manipulate(
                "DELETE FROM lng_modules" .
                " WHERE module = " . $ilDB->quote($prefix, "text")
            );

            $this->removeMigratedMoFiles($prefix);
        }
    }

    /**
     * Overlay counterpart to the DB deletes, like ilObjLanguage::removeMigratedMoFiles(): without it a migrated
     * plugin's overlay would still be served, as ilLanguage does not check whether $prefix still belongs to an
     * installed plugin.
     *
     * Iterates every language the plugin ships (see getAvailableLangFiles()), not only installed ones: an overlay can
     * exist for a language uninstalled later; removeOverlay() is a no-op when there is nothing to remove.
     * Without a LanguageFileDirectoryManager nothing happens; a removal failure per language is logged and
     * swallowed, the DB-side uninstall must not be blocked by it.
     */
    private function removeMigratedMoFiles(string $prefix): void
    {
        global $DIC;

        if (!$DIC->offsetExists(LanguageFileDirectoryManager::class)) {
            return;
        }
        // a prefix that cannot be a module name is not looked up at all
        if (preg_match('/^[A-Za-z0-9_]+\z/', $prefix) !== 1) {
            $DIC->logger()->forComponent('lang')->warning(PlainLogText::of(sprintf(
                'Not removing any overlay for the plugin language module "%s": not a valid module name.',
                $prefix
            )));
            return;
        }

        /** @var LanguageFileDirectoryManager $manager */
        $manager = $DIC[LanguageFileDirectoryManager::class];
        $client_data_dir = MigratedLanguageFilePaths::resolveClientDataDir(ILIAS_ABSOLUTE_PATH);

        foreach ($this->getAvailableLangFiles() as $lang) {
            try {
                MigratedLanguageFileSync::removeOverlay($manager, $lang['key'], $prefix, $client_data_dir);
            } catch (\Throwable $t) {
                $DIC->logger()->forComponent('lang')->warning(sprintf(
                    'Could not remove migrated overlay file for module "%s", language "%s": %s',
                    $prefix,
                    $lang['key'],
                    $t->getMessage()
                ));
            }
        }
    }

    /**
     * Load language module for plugin
     */
    public function loadLanguageModule(): void
    {
        global $DIC;
        $lng = $DIC->language();

        if (is_object($lng)) {
            $lng->loadLanguageModule($this->getPrefix());
        }
    }

    /**
     * Get Language Variable (prefix will be prepended automatically)
     */
    public function txt(string $a_var): string
    {
        global $DIC;
        $lng = $DIC->language();
        $this->loadLanguageModule();

        $key = $this->getPrefix() . "_" . $a_var;
        $text = $lng->txt($key);
        if ($text !== "-" . $key . "-" || !$lng instanceof ilLanguage) {
            return $text;
        }

        // plugin language files are often incomplete: the default language fills the gap, as before
        return ilLanguage::_lookupEntry($lng->getDefaultLanguage(), $this->getPrefix(), $key);
    }
}
