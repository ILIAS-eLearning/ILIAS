<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class handles translation mode for an object.
 *
 * Objects may not use any translations at all
 * - use translations for title/description only or
 * - use translation for (the page editing) content, too.
 *
 * Currently, supported by container objects and ILIAS learning modules.
 *
 * Content master lang vs. default language
 * - If no translation mode for the content is active no master lang will be
 *   set and no record in table obj_content_master_lng will be saved. For the
 *   title/descriptions the default will be marked by field lang_default in table
 *   object_translation.
 * - If translation for content is activated a master language must be set (since
 *   consent may already exist the language of this content is defined through
 *   setting the master language (in obj_content_master_lng). Modules that use
 *   this mode will not get informed about this, so they can not internally
 *   assign existing content to the master lang
 * - If translation for content is activated additionally a fallback language
 *   can be defined. Users will be presented their language, if content available
 *   otherwise the fallback language, if content is available, otherwise the
 *   master language
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectTranslation
{
    protected static array $instances = [];

    protected ilDBInterface $db;
    protected int $obj_id;

    protected string $master_lang = "";
    protected array $languages = [];
    protected bool $content_activated = false;
    protected string $fallback_language = "";

    private function __construct(int $obj_id)
    {
        global $DIC;
        $this->db = $DIC->database();

        $this->setObjId($obj_id);

        if ($this->getObjId() <= 0) {
            throw new ilObjectException("ilObjectTranslation: No object ID passed.");
        }

        $this->read();
    }

    public static function getInstance(int $obj_id) : ilObjectTranslation
    {
        if (!isset(self::$instances[$obj_id])) {
            self::$instances[$obj_id] = new ilObjectTranslation($obj_id);
        }

        return self::$instances[$obj_id];
    }

    public function setObjId(int $val) : void
    {
        $this->obj_id = $val;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setMasterLanguage(string $val) : void
    {
        $this->master_lang = $val;
    }

    public function getMasterLanguage() : string
    {
        return $this->master_lang;
    }

    /**
     * @param array $val array of language codes
     */
    public function setLanguages(array $val) : void
    {
        $this->languages = $val;
    }

    /**
     * @return array array of language codes
     */
    public function getLanguages() : array
    {
        return $this->languages;
    }

    public function setFallbackLanguage(string $val) : void
    {
        $this->fallback_language = $val;
    }

    public function getFallbackLanguage() : string
    {
        return $this->fallback_language;
    }

    public function addLanguage(
        string $lang,
        string $title,
        string $description,
        bool $default,
        bool $force = false
    ) : void {
        if ($lang != "" && (!isset($this->languages[$lang]) || $force)) {
            if ($default) {
                foreach ($this->languages as $k => $l) {
                    $this->languages[$k]["lang_default"] = false;
                }
            }
            $this->languages[$lang] = [
                "lang_code" => $lang,
                "lang_default" => $default,
                "title" => $title,
                "description" => $description
            ];
        }
    }

    public function getDefaultTitle() : string
    {
        foreach ($this->languages as $l) {
            if ($l["lang_default"]) {
                return $l["title"];
            }
        }
        if (count($this->languages) == 0) {
            return ilObject::_lookupTitle($this->getObjId());
        }
        return "";
    }

    public function setDefaultTitle(string $title) : void
    {
        foreach ($this->languages as $k => $l) {
            if ($l["lang_default"]) {
                $this->languages[$k]["title"] = $title;
            }
        }
    }

    public function getDefaultDescription() : string
    {
        foreach ($this->languages as $l) {
            if ($l["lang_default"]) {
                return $l["description"];
            }
        }
        if (count($this->languages) == 0) {
            return ilObject::_lookupDescription($this->getObjId());
        }
        return "";
    }

    public function setDefaultDescription(string $description) : void
    {
        foreach ($this->languages as $k => $l) {
            if ($l["lang_default"]) {
                $this->languages[$k]["description"] = $description;
            }
        }
    }

    public function getDefaultLanguage() : string
    {
        foreach ($this->languages as $l) {
            if ($l["lang_default"]) {
                return $l["lang_code"];
            }
        }
        return "";
    }

    public function removeLanguage(string $lang) : void
    {
        if ($lang != $this->getMasterLanguage()) {
            unset($this->languages[$lang]);
        }
    }

    protected function setContentActivated(bool $val) : void
    {
        $this->content_activated = $val;
    }

    public function getContentActivated() : bool
    {
        return $this->content_activated;
    }

    public function read() : void
    {
        $sql =
            "SELECT obj_id, master_lang, fallback_lang" . PHP_EOL
            . "FROM obj_content_master_lng" . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer") . PHP_EOL
        ;
        $result = $this->db->query($sql);
        if ($row = $this->db->fetchAssoc($result)) {
            $this->setMasterLanguage($row["master_lang"]);
            $this->setFallbackLanguage($row["fallback_lang"] ?? '');
            $this->setContentActivated(true);
        } else {
            $this->setContentActivated(false);
        }

        $this->setLanguages([]);

        $sql =
            "SELECT title, description, lang_code, lang_default" . PHP_EOL
            . "FROM object_translation" . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer") . PHP_EOL
        ;
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($result)) {
            $this->addLanguage($row["lang_code"], $row["title"], $row["description"], (bool) $row["lang_default"]);
        }
    }

    public function delete() : void
    {
        $this->db->manipulate(
            "DELETE FROM obj_content_master_lng " .
            " WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer")
        );
        $this->db->manipulate(
            "DELETE FROM object_translation " .
            " WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer")
        );
    }

    public function deactivateContentTranslation() : void
    {
        $this->db->manipulate(
            "DELETE FROM obj_content_master_lng " .
            " WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer")
        );
    }

    public function save() : void
    {
        $this->delete();

        if ($this->getMasterLanguage() != "") {
            $values = [
                "obj_id" => ["integer", $this->getObjId()],
                "master_lang" => ["text", $this->getMasterLanguage()],
                "fallback_lang" => ["text", $this->getFallbackLanguage()]
            ];

            $this->db->insert("obj_content_master_lng", $values);
            // ensure that an entry for the master language exists and is the default
            if (!isset($this->languages[$this->getMasterLanguage()])) {
                $this->languages[$this->getMasterLanguage()] = array("title" => "",
                    "description" => "", "lang_code" => $this->getMasterLanguage(), "lang_default" => 1);
            }
            foreach ($this->languages as $l => $trans) {
                if ($l == $this->getMasterLanguage()) {
                    $this->languages[$l]["lang_default"] = 1;
                } else {
                    $this->languages[$l]["lang_default"] = 0;
                }
            }
        }

        foreach ($this->getLanguages() as $l => $trans) {
            $values = [
                "obj_id" => ["integer", $this->getObjId()],
                "title" => ["text", $trans["title"]],
                "description" => ["text", $trans["description"]],
                "lang_code" => ["text", $l],
                "lang_default" => ["integer", $trans["lang_default"]],
            ];
            $this->db->insert("object_translation", $values);
        }
    }

    /**
     * Copy multilingual settings
     */
    public function copy(int $obj_id) : ilObjectTranslation
    {
        $target_ml = new ilObjectTranslation($obj_id);
        $target_ml->setMasterLanguage($this->getMasterLanguage());
        $target_ml->setFallbackLanguage($this->getFallbackLanguage());
        $target_ml->setLanguages($this->getLanguages());
        $target_ml->save();
        return $target_ml;
    }


    /**
     * Get effective language for given language. This checks if
     * - multilingual is activated and
     * - the given language is part of the available translations
     * If not a "-" is returned (master language).
     *
     * @param string $lang language
     * @param string $parent_type page parent type
     * @return string effective language ("-" for master)
     */
    public function getEffectiveContentLang(string $lang, string $parent_type) : string
    {
        $langs = $this->getLanguages();
        $page_lang_key = ($lang == $this->getMasterLanguage())
            ? "-"
            : $lang;
        if ($this->getContentActivated() &&
            isset($langs[$lang]) &&
            ilPageObject::_exists($parent_type, $this->getObjId(), $page_lang_key)) {
            if ($lang == $this->getMasterLanguage()) {
                return "-";
            }
            return $lang;
        }
        if ($this->getContentActivated() &&
            isset($langs[$this->getFallbackLanguage()]) &&
            ilPageObject::_exists($parent_type, $this->getObjId(), $this->getFallbackLanguage())) {
            return $this->getFallbackLanguage();
        }
        return "-";
    }
}
