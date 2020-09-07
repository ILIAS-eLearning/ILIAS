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
class ilMultilingualism
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $db;
    protected $obj_id;
    protected $languages = array();
    protected $type = "";
    protected static $instances = array();

    /**
     * Constructor
     *
     * @param int $a_obj_id object id
     * @param string $a_type id type
     * @throws ilObjectException
     */
    private function __construct($a_obj_id, $a_type)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $ilDB = $DIC->database();

        $this->db = $ilDB;

        $this->setObjId($a_obj_id);
        $this->setType($a_type);

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
     * @return ilMultilingualism translation object
     */
    public static function getInstance($a_obj_id, $a_type)
    {
        if (!isset(self::$instances[$a_type][$a_obj_id])) {
            self::$instances[$a_type][$a_obj_id] = new self($a_obj_id, $a_type);
        }

        return self::$instances[$a_type][$a_obj_id];
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getDefaultLanguage()
    {
        $lng = $this->lng;
        
        foreach ($this->languages as $k => $v) {
            if ($v["lang_default"]) {
                return $k;
            }
        }
        
        return $lng->getDefaultLanguage();
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
     * Remove language
     *
     * @param string $a_lang language code
     */
    public function removeLanguage($a_lang)
    {
        if ($a_lang != $this->getDefaultLanguage()) {
            unset($this->languages[$a_lang]);
        }
    }

    /**
     * Read
     */
    public function read()
    {
        $this->setLanguages(array());
        $set = $this->db->query(
            "SELECT * FROM il_translations " .
            " WHERE id = " . $this->db->quote($this->getObjId(), "integer") .
            " AND id_type = " . $this->db->quote($this->getType(), "text")
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
            "DELETE FROM il_translations " .
            " WHERE id = " . $this->db->quote($this->getObjId(), "integer") .
            " AND id_type = " . $this->db->quote($this->getType(), "text")
        );
    }

    /**
     * Save
     */
    public function save()
    {
        $this->delete();

        foreach ($this->getLanguages() as $l => $trans) {
            $this->db->manipulate($t = "INSERT INTO il_translations " .
                "(id, id_type, title, description, lang_code, lang_default) VALUES (" .
                $this->db->quote($this->getObjId(), "integer") . "," .
                $this->db->quote($this->getType(), "text") . "," .
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
     * @param string $a_type parent object type
     * @param int $a_obj_id parent object id
     * @return ilObjectTranslation target multilang object
     */
    public function copy($a_obj_id)
    {
        $target_ml = new self($a_obj_id, $this->getType());
        $target_ml->setLanguages($this->getLanguages());
        $target_ml->save();
        return $target_ml;
    }


    
    /**
     * Export
     * @param ilXmlWriter $writer
     * @return ilXmlWriter
     */
    public function toXml(ilXmlWriter $writer)
    {
        $writer->xmlStartTag('translations');
        
        foreach ($this->getLanguages() as $k => $v) {
            $writer->xmlStartTag('translation', array('language' => $k, 'default' => $v['lang_default'] ? 1 : 0));
            $writer->xmlElement('title', array(), $v['title']);
            $writer->xmlElement('description', array(), $v['description']);
            $writer->xmlEndTag('translation');
        }
        $writer->xmlEndTag('translations');

        return $writer;
    }

    /**
     * xml import
     *
     * @param SimpleXMLElement $root
     * @return mixed
     */
    public function fromXML(SimpleXMLElement $root)
    {
        if ($root->translations) {
            $root = $root->translations;
        }
        
        foreach ($root->translation as $trans) {
            $this->addLanguage(
                (string) trim($trans["language"]),
                (string) trim($trans->title),
                (string) trim($trans->description),
                (int) $trans["default"] != 0?true:false
            );
        }
    }
}
