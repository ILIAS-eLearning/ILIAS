<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class handles translation mode for an object.
 *
 * Objects may not use any translations at all
 * - use translations for title/description only or
 * - use translation for (the page editing) content, too.
 *
 * Currently supported by container objects and ILIAS learning modules.
 *
 * Content master lang vs. default language
 * - If no translation mode for the content is active no master lang will be
 *   set and no record in table obj_content_master_lng will be saved. For the
 *   title/descriptions the default will be marked by field lang_default in table
 *   object_translation.
 * - If translation for content is activated a master language must be set (since
 *   concent may already exist the language of this content is defined through
 *   setting the master language (in obj_content_master_lng). Modules that use
 *   this mode will not get informed about this, so they can not internally
 *   assign existing content to the master lang
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesObject
 */
class ilObjectTranslation
{
    protected $db;
    protected $obj_id;
    protected $master_lang;
    protected $languages = array();
    protected $content_activated = false;
    protected static $instances = array();

    /**
     * Constructor
     *
     * @param int $a_obj_id object id
     * @throws ilObjectException
     */
    private function __construct($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->db = $ilDB;

        $this->setObjId($a_obj_id);

        if ($this->getObjId() <= 0) {
            include_once("./Services/Object/exceptions/class.ilObjectException.php");
            throw new ilObjectException("ilObjectTranslation: No object ID passed.");
        }

        $this->read();
    }

    /**
     * Get instance
     *
     * @param integer $a_obj_id (repository) object id
     * @return ilObjectTranslation translation object
     */
    public static function getInstance($a_obj_id)
    {
        if (!isset(self::$instances[$a_obj_id])) {
            self::$instances[$a_obj_id] = new ilObjectTranslation($a_obj_id);
        }

        return self::$instances[$a_obj_id];
    }


    /**
     * Set object id
     *
     * @param int $a_val object id
     */
    public function setObjId($a_val)
    {
        $this->obj_id = $a_val;
    }

    /**
     * Get object id
     *
     * @return int object id
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Set master language
     *
     * @param string $a_val master language
     */
    public function setMasterLanguage($a_val)
    {
        $this->master_lang = $a_val;
    }

    /**
     * Get master language
     *
     * @return string master language
     */
    public function getMasterLanguage()
    {
        return $this->master_lang;
    }

    /**
     * Set languages
     *
     * @param array $a_val array of language codes
     */
    public function setLanguages(array $a_val)
    {
        $this->languages = $a_val;
    }

    /**
     * Get languages
     *
     * @return array array of language codes
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Add language
     *
     * @param string $a_lang language
     * @param string $a_title title
     * @param string $a_description description
     * @param bool $a_default default language?
     */
    public function addLanguage($a_lang, $a_title, $a_description, $a_default, $a_force = false)
    {
        if ($a_lang != "" && (!isset($this->languages[$a_lang]) || $a_force)) {
            if ($a_default) {
                foreach ($this->languages as $k => $l) {
                    $this->languages[$k]["lang_default"] = false;
                }
            }
            $this->languages[$a_lang] = array("lang_code" => $a_lang, "lang_default" => $a_default,
                "title" => $a_title, "description" => $a_description);
        }
    }

    /**
     * Get default title
     *
     * @return string title of default language
     */
    public function getDefaultTitle()
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

    /**
     * Set default title
     *
     * @param string $a_title title
     */
    public function setDefaultTitle($a_title)
    {
        foreach ($this->languages as $k => $l) {
            if ($l["lang_default"]) {
                $this->languages[$k]["title"] = $a_title;
            }
        }
    }

    /**
     * Get default description
     *
     * @return string description of default language
     */
    public function getDefaultDescription()
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

    /**
     * Set default description
     *
     * @param string $a_description description
     */
    public function setDefaultDescription($a_description)
    {
        foreach ($this->languages as $k => $l) {
            if ($l["lang_default"]) {
                $this->languages[$k]["description"] = $a_description;
            }
        }
    }

    /**
     * Get default language
     *
     * @return string default language
     */
    public function getDefaultLanguage()
    {
        foreach ($this->languages as $l) {
            if ($l["lang_default"]) {
                return $l["lang_code"];
            }
        }
        return "";
    }


    /**
     * Remove language
     *
     * @param string $a_lang language code
     */
    public function removeLanguage($a_lang)
    {
        if ($a_lang != $this->getMasterLanguage()) {
            unset($this->languages[$a_lang]);
        }
    }


    /**
     * Set activated for content
     *
     * @param bool $a_val activated for content?
     */
    protected function setContentActivated($a_val)
    {
        $this->content_activated = $a_val;
    }

    /**
     * Get activated for content
     *
     * @return bool activated for content?
     */
    public function getContentActivated()
    {
        return $this->content_activated;
    }

    /**
     * Read
     */
    public function read()
    {
        $set = $this->db->query(
            "SELECT * FROM obj_content_master_lng " .
            " WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer")
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            $this->setMasterLanguage($rec["master_lang"]);
            $this->setContentActivated(true);
        } else {
            $this->setContentActivated(false);
        }

        $this->setLanguages(array());
        $set = $this->db->query(
            "SELECT * FROM object_translation " .
            " WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer")
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->addLanguage($rec["lang_code"], $rec["title"], $rec["description"], $rec["lang_default"]);
        }
    }

    /**
     * Delete
     */
    public function delete()
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

    /**
     * Deactivate content translation
     */
    public function deactivateContentTranslation()
    {
        $this->db->manipulate(
            "DELETE FROM obj_content_master_lng " .
            " WHERE obj_id = " . $this->db->quote($this->getObjId(), "integer")
        );
    }

    /**
     * Save
     */
    public function save()
    {
        $this->delete();

        if ($this->getMasterLanguage() != "") {
            $this->db->manipulate("INSERT INTO obj_content_master_lng " .
                "(obj_id, master_lang) VALUES (" .
                $this->db->quote($this->getObjId(), "integer") . "," .
                $this->db->quote($this->getMasterLanguage(), "text") .
                ")");

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
            $this->db->manipulate($t = "INSERT INTO object_translation " .
                "(obj_id, title, description, lang_code, lang_default) VALUES (" .
                $this->db->quote($this->getObjId(), "integer") . "," .
                $this->db->quote($trans["title"], "text") . "," .
                $this->db->quote($trans["description"], "text") . "," .
                $this->db->quote($l, "text") . "," .
                $this->db->quote($trans["lang_default"], "integer") .
                ")");
        }
    }

    /**
     * Copy multilinguality settings
     *
     * @param string $a_target_parent_type parent object type
     * @param int $a_target_parent_id parent object id
     * @return ilObjectTranslation target multilang object
     */
    public function copy($a_obj_id)
    {
        $target_ml = new ilObjectTranslation($a_obj_id);
        $target_ml->setMasterLanguage($this->getMasterLanguage());
        $target_ml->setLanguages($this->getLanguages());
        $target_ml->save();
        return $target_ml;
    }


    /**
     * Get effective language for given language. This checks if
     * - multilinguality is activated and
     * - the given language is part of the available translations
     * If not a "-" is returned (master language).
     *
     * @param string $a_lang language
     * @param string $a_parent_type page parent type
     * @return string effective language ("-" for master)
     */
    public function getEffectiveContentLang($a_lang, $a_parent_type)
    {
        $langs = $this->getLanguages();
        if ($this->getContentActivated() &&
            isset($langs[$a_lang]) &&
            ilPageObject::_exists($a_parent_type, $this->getObjId(), $a_lang)) {
            return $a_lang;
        }
        return "-";
    }
}
