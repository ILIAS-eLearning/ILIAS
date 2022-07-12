<?php declare(strict_types=1);

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
 * language handling
 *
 * this class offers the language handling for an application.
 * it works initially on one file: languages.txt
 * from this file the class can generate many single language files.
 * the constructor is called with a small language abbreviation
 * e.g. $lng = new Language("en");
 * the constructor reads the single-languagefile en.lang and puts this into an array.
 * with
 * e.g. $lng->txt("user_updated");
 * you can translate a lang-topic into the actual language
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @version $Id$
 *
 *
 * @todo Das Datefeld wird bei Aenderungen einer Sprache (update, install, deinstall) nicht richtig gesetzt!!!
 *  Die Formatfunktionen gehoeren nicht in class.Language. Die sind auch woanders einsetzbar!!!
 *  Daher->besser in class.Format
 */
class ilLanguage
{
    public ILIAS $ilias;
    public array $text = [];
    public string $lang_default;
    public string $lang_user;
    public string $lang_path;
    public string $lang_key;
    public string $lang_name;
    public string $separator = "#:#";
    public string $comment_separator = "###";
    public array $loaded_modules = array();
    protected static array $used_topics = array();
    protected static array $used_modules = array();
    protected array $cached_modules = array();
    protected array $map_modules_txt = array();
    protected bool $usage_log_enabled = false;
    protected static array $lng_log = array();
    protected string $cust_lang_path;
    protected ilLogger $log;
    protected ilCachedLanguage $global_cache;

    /**
     * Constructor
     * read the single-language file and put this in an array text.
     * the text array is two-dimensional. First dimension is the language.
     * Second dimension is the languagetopic. Content is the translation.
     *
     * $a_lang_key    language code (two characters), e.g. "de", "en", "in"
     * Return false if reading failed, otherwise true
     */
    public function __construct(string $a_lang_key)
    {
        global $DIC;
        $client_ini = $DIC->clientIni();

        $this->log = $DIC->logger()->root();

        $this->lang_key = $a_lang_key;

        $this->usage_log_enabled = self::isUsageLogEnabled();

        $this->lang_path = ILIAS_ABSOLUTE_PATH . "/lang";
        $this->cust_lang_path = ILIAS_ABSOLUTE_PATH . "/Customizing/global/lang";

        $this->lang_default = $client_ini->readVariable("language", "default") ?: 'en';

        if ($DIC->offsetExists("ilSetting")) {
            $ilSetting = $DIC->settings();
            if ($ilSetting->get("language") != "") {
                $this->lang_default = $ilSetting->get("language");
            }
        }
        if ($DIC->offsetExists("ilUser")) {
            $ilUser = $DIC->user();
            $this->lang_user = $ilUser->prefs["language"];
        }

        $langs = $this->getInstalledLanguages();

        if (!in_array($this->lang_key, $langs, true)) {
            $this->lang_key = $this->lang_default;
        }
    
        require_once("./Services/Language/classes/class.ilCachedLanguage.php");
        $this->global_cache = ilCachedLanguage::getInstance($this->lang_key);
        if ($this->global_cache->isActive()) {
            $this->cached_modules = $this->global_cache->getTranslations();
        }
        $this->loadLanguageModule("common");
    }

    /**
     * Return lang key
     */
    public function getLangKey() : string
    {
        return $this->lang_key;
    }

    /**
     * Return default language
     */
    public function getDefaultLanguage() : string
    {
        return $this->lang_default ?: "en";
    }

    /**
     * Return text direction
     */
    public function getTextDirection() : string
    {
        $rtl = array("ar", "fa", "ur", "he");
        if (in_array($this->getContentLanguage(), $rtl)) {
            return "rtl";
        }
        return "ltr";
    }

    /**
     * Return content language
     */
    public function getContentLanguage() : string
    {
        if ($this->getUserLanguage()) {
            return $this->getUserLanguage();
        }
        return $this->getLangKey();
    }

    /**
     * gets the text for a given topic in a given language
     * if the topic is not in the list, the topic itself with "-" will be returned
     */
    public function txtlng(string $a_module, string $a_topic, string $a_language) : string
    {
        if (strcmp($a_language, $this->lang_key) === 0) {
            return $this->txt($a_topic);
        } else {
            return self::_lookupEntry($a_language, $a_module, $a_topic);
        }
    }

    /**
     * gets the text for a given topic
     * if the topic is not in the list, the topic itself with "-" will be returned
     */
    public function txt(string $a_topic, string $a_default_lang_fallback_mod = "") : string
    {
        if (empty($a_topic)) {
            return "";
        }

        // remember the used topics
        self::$used_topics[$a_topic] = $a_topic;

        $translation = $this->text[$a_topic] ?? "";

        if ($translation === "" && $a_default_lang_fallback_mod !== "") {
            // #13467 - try current language first (could be missing module)
            if ($this->lang_key != $this->lang_default) {
                $translation = self::_lookupEntry(
                    $this->lang_key,
                    $a_default_lang_fallback_mod,
                    $a_topic
                );
            }
            // try default language last
            if ($translation === "" || $translation === "-" . $a_topic . "-") {
                $translation = self::_lookupEntry(
                    $this->lang_default,
                    $a_default_lang_fallback_mod,
                    $a_topic
                );
            }
        }


        if ($translation === "") {
            if (ILIAS_LOG_ENABLED && is_object($this->log)) {
                $this->log->debug("Language (" . $this->lang_key . "): topic -" . $a_topic . "- not present");
            }
            return "-" . $a_topic . "-";
        }

        if ($this->usage_log_enabled) {
            self::logUsage($this->map_modules_txt[$a_topic], $a_topic);
        }

        return $translation;
    }

    /**
     * Check if language entry exists
     */
    public function exists(string $a_topic) : bool
    {
        return isset($this->text[$a_topic]);
    }

    /**
     * Load language module
     */
    public function loadLanguageModule(string $a_module) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        if (in_array($a_module, $this->loaded_modules, true)) {
            return;
        }

        $this->loaded_modules[] = $a_module;

        // remember the used modules globally
        self::$used_modules[$a_module] = $a_module;

        $lang_key = $this->lang_key;

        if (empty($this->lang_key)) {
            $lang_key = $this->lang_user;
        }

        if (isset($this->cached_modules[$a_module]) && is_array($this->cached_modules[$a_module])) {
            $this->text = array_merge($this->text, $this->cached_modules[$a_module]);

            if ($this->usage_log_enabled) {
                foreach (array_keys($this->cached_modules[$a_module]) as $key) {
                    $this->map_modules_txt[$key] = $a_module;
                }
            }

            return;
        }

        $q = "SELECT * FROM lng_modules " .
                "WHERE lang_key = " . $ilDB->quote($lang_key, "text") . " AND module = " .
                $ilDB->quote($a_module, "text");
        $r = $ilDB->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        if ($row === false) {
            return;
        }

        $new_text = unserialize($row["lang_array"], ["allowed_classes" => false]);
        if (is_array($new_text)) {
            $this->text = array_merge($this->text, $new_text);

            if ($this->usage_log_enabled) {
                foreach (array_keys($new_text) as $key) {
                    $this->map_modules_txt[$key] = $a_module;
                }
            }
        }
    }

    /**
     * Get installed languages
     */
    public function getInstalledLanguages() : array
    {
        return self::_getInstalledLanguages();
    }

    /**
     * Get installed languages
     */
    public static function _getInstalledLanguages() : array
    {
        include_once "./Services/Object/classes/class.ilObject.php";
        $langlist = ilObject::_getObjectsByType("lng");

        $languages = [];
        foreach ($langlist as $lang) {
            if (strpos($lang["desc"], "installed") === 0) {
                $languages[] = $lang["title"];
            }
        }

        return $languages ?: [];
    }

    public static function _lookupEntry(string $a_lang_key, string $a_mod, string $a_id) : string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query($q = sprintf(
            "SELECT * FROM lng_data WHERE module = %s " .
            "AND lang_key = %s AND identifier = %s",
            $ilDB->quote($a_mod, "text"),
            $ilDB->quote($a_lang_key, "text"),
            $ilDB->quote($a_id, "text")
        ));
        $rec = $ilDB->fetchAssoc($set);

        if (isset($rec["value"]) && $rec["value"] != "") {
            // remember the used topics
            self::$used_topics[$a_id] = $a_id;
            self::$used_modules[$a_mod] = $a_mod;

            if (self::isUsageLogEnabled()) {
                self::logUsage($a_mod, $a_id);
            }

            return $rec["value"];
        }

        return "-" . $a_id . "-";
    }

    /**
     * Lookup obj_id of language
     */
    public static function lookupId(string $a_lang_key) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "SELECT obj_id FROM object_data " . " " .
        "WHERE title = " . $ilDB->quote($a_lang_key, "text") . " " .
            "AND type = " . $ilDB->quote("lng", "text");

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->obj_id;
        }
        return 0;
    }

    /**
     * Return used topics
     */
    public function getUsedTopics() : array
    {
        asort(self::$used_topics);
        return self::$used_topics;
    }

    /**
     * Return used modules
     */
    public function getUsedModules() : array
    {
        asort(self::$used_modules);
        return self::$used_modules;
    }

    /**
     * Return language of user
     */
    public function getUserLanguage() : string
    {
        return $this->lang_user;
    }

    public function getCustomLangPath() : string
    {
        return $this->cust_lang_path;
    }

    /**
     * Builds a global default language instance
     */
    public static function getFallbackInstance() : ilLanguage
    {
        return new self("en");
    }

    /**
     * Builds the global language object
     */
    public static function getGlobalInstance() : self
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $ilUser = null;
        if ($DIC->offsetExists("ilUser")) {
            $ilUser = $DIC->user();
        }

        $isset_get_lang = $DIC->http()->wrapper()->query()->has("lang");
        if (!ilSession::get("lang") && !$isset_get_lang && $ilUser instanceof ilObjUser &&
            (!$ilUser->getId() || $ilUser->isAnonymous())) {
            $language_detection = new ilLanguageDetection();
            $language = $language_detection->detect();

            $ilUser->setPref("language", $language);
        }

        $post_change_lang_to = [];
        if ($DIC->http()->wrapper()->post()->has('change_lang_to')) {
            $post_change_lang_to = $DIC->http()->wrapper()->post()->retrieve(
                'change_lang_to',
                $DIC->refinery()->kindlyTo()->dictOf(
                    $DIC->refinery()->kindlyTo()->float()
                )
            );
        }

        // prefer personal setting when coming from login screen
        // Added check for ilUser->getId > 0 because it is 0 when the language is changed and
        // the terms of service should be displayed
        if ($ilUser instanceof ilObjUser &&
            ($ilUser->getId() && !$ilUser->isAnonymous())
        ) {
            ilSession::set("lang", $ilUser->getPref("language"));
        }

        $get_lang = null;
        if ($isset_get_lang) {
            $get_lang = $DIC->http()->wrapper()->query()->retrieve(
                "lang",
                $DIC->refinery()->kindlyTo()->string()
            );
        }
        ilSession::set("lang", ($isset_get_lang && $get_lang) ? $get_lang : ilSession::get("lang"));

        // check whether lang selection is valid
        $langs = self::_getInstalledLanguages();
        if (!in_array(ilSession::get("lang"), $langs, true)) {
            if ($ilSetting instanceof ilSetting && (string) $ilSetting->get("language", '') !== "") {
                ilSession::set("lang", $ilSetting->get("language"));
            } else {
                ilSession::set("lang", $langs[0]);
            }
        }

        return new self(ilSession::get("lang"));
    }

    /**
     * Transfer text to Javascript
     *
     * @param string|string[] $a_lang_key
     * $a_lang_key language key string or array of language keys
     */

    public function toJS($a_lang_key, ilGlobalTemplateInterface $a_tpl = null) : void
    {
        global $DIC;
        $tpl = $DIC["tpl"];

        if (!is_object($a_tpl)) {
            $a_tpl = $tpl;
        }

        if (!is_array($a_lang_key)) {
            $a_lang_key = array($a_lang_key);
        }

        $map = array();
        foreach ($a_lang_key as $lk) {
            $map[$lk] = $this->txt($lk);
        }
        $this->toJSMap($map, $a_tpl);
    }

    /**
     * Transfer text to Javascript
     *
     * $a_map array of key value pairs (key is text string, value is content)
     */
    public function toJSMap(array $a_map, ilGlobalTemplateInterface $a_tpl = null) : void
    {
        global $DIC;
        $tpl = $DIC["tpl"];

        if (!is_object($a_tpl)) {
            $a_tpl = $tpl;
        }

        if (!is_array($a_map)) {
            return;
        }

        foreach ($a_map as $k => $v) {
            if ($v != "") {
                $a_tpl->addOnloadCode("il.Language.setLangVar('" . $k . "', " . json_encode($v, JSON_THROW_ON_ERROR) . ");");
            }
        }
    }

    /**
     * saves tupel of language module and identifier
     */
    protected static function logUsage(string $a_module, string $a_identifier) : void
    {
        if ($a_module !== "" && $a_identifier !== "") {
            self::$lng_log[$a_identifier] = $a_module;
        }
    }

    /**
     * checks if language usage log is enabled
     * you need MySQL to use this function
     * this function is automatically enabled if DEVMODE is on
     * this function is also enabled if language_log is 1
     */
    protected static function isUsageLogEnabled() : bool
    {
        global $DIC;
        $ilClientIniFile = $DIC->clientIni();
        $ilDB = $DIC->database();

        if (!$ilClientIniFile instanceof ilIniFile) {
            return false;
        }

        if (defined("DEVMODE") && DEVMODE) {
            return true;
        }

        if (!$ilClientIniFile->variableExists("system", "LANGUAGE_LOG")) {
            return (int) $ilClientIniFile->readVariable("system", "LANGUAGE_LOG") === 1;
        }
        return false;
    }

    /**
     * destructor saves all language usages to db if log is enabled and ilDB exists
     */
    public function __destruct()
    {
        global $DIC;

        //case $ilDB not existing should not happen but if something went wrong it shouldn't leads to any failures
        if (!$this->usage_log_enabled || !$DIC->isDependencyAvailable("database")) {
            return;
        }

        $ilDB = $DIC->database();

        foreach (self::$lng_log as $identifier => $module) {
            $wave[] = "(" . $ilDB->quote($module, "text") . ', ' . $ilDB->quote($identifier, "text") . ")";
            unset(self::$lng_log[$identifier]);

            if (count($wave) === 150 || (count(self::$lng_log) === 0 && count($wave) > 0)) {
                $query = "REPLACE INTO lng_log (module, identifier) VALUES " . implode(", ", $wave);
                $ilDB->manipulate($query);

                $wave = array();
            }
        }
    }
} // END class.Language
