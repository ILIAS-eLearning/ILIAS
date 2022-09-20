<?php

declare(strict_types=1);

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
 ********************************************************************
 */

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
     * @return array of [key => "en" (e.g.), file => ...]
     */
    public function getAvailableLangFiles(): array
    {
        $directory = $this->getLanguageDirectory();
        if (!@is_dir($directory)) {
            return [];
        }

        $langs = [];

        $dir = opendir($directory);
        while ($file = readdir($dir)) {
            if ($file === "." || $file === "..") {
                continue;
            }

            // directories
            if (@is_file($directory . "/" . $file) &&
                strpos($file, "ilias_") === 0 &&
                substr($file, strlen($file) - 5) === ".lang") {
                $langs[] = [
                    "key" => substr($file, 6, 2),
                    "file" => $file
                ];
            }
        }

        return $langs;
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
        ilGlobalCache::flushAll();

        // get the keys of all installed languages if keys are not provided
        if (!isset($a_lang_keys)) {
            $a_lang_keys = [];
            foreach (ilObjLanguage::getInstalledLanguages() as $langObj) {
                if ($langObj->isInstalled()) {
                    $a_lang_keys[] = $langObj->getKey();
                }
            }
        }

        $langs = $this->getAvailableLangFiles();

        $prefix = $this->getPrefix();

        foreach ($langs as $lang) {
            // check if the language should be updated, otherwise skip it
            if (!in_array($lang['key'], $a_lang_keys, true)) {
                continue;
            }

            $txt = file($this->getLanguageDirectory() . "/" . $lang["file"]);
            $lang_array = [];

            // get locally changed variables of the module (these should be kept)
            $local_changes = ilObjLanguage::_getLocalChangesByModule($lang['key'], $prefix);

            // get language data
            if (is_array($txt)) {
                foreach ($txt as $row) {
                    if ($row[0] !== "#" && strpos($row, "#:#") > 0) {
                        $a = explode("#:#", trim($row));
                        $identifier = $prefix . "_" . trim($a[0]);
                        $value = trim($a[1]);

                        if (isset($local_changes[$identifier])) {
                            $lang_array[$identifier] = $local_changes[$identifier];
                        } else {
                            $lang_array[$identifier] = $value;
                            ilObjLanguage::replaceLangEntry($prefix, $identifier, $lang["key"], $value);
                        }
                        //echo "<br>-$prefix-".$prefix."_".trim($a[0])."-".$lang["key"]."-";
                    }
                }
            }

            ilObjLanguage::replaceLangModule($lang["key"], $prefix, $lang_array);
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

        return $lng->txt($this->getPrefix() . "_" . $a_var, $this->getPrefix());
    }
}
