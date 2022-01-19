<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMultilingualism
{
    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected int $obj_id;
    protected array $languages = array();
    protected string $type = "";
    protected static array $instances = array();

    /**
     * @throws ilObjectException
     */
    private function __construct(
        int $a_obj_id,
        string $a_type
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $ilDB = $DIC->database();

        $this->db = $ilDB;

        $this->setObjId($a_obj_id);
        $this->setType($a_type);

        if ($this->getObjId() <= 0) {
            throw new ilObjectException("ilObjectTranslation: No object ID passed.");
        }

        $this->read();
    }

    /**
     * @param int $a_obj_id (repository) object id
     */
    public static function getInstance(int $a_obj_id, string $a_type) : self
    {
        if (!isset(self::$instances[$a_type][$a_obj_id])) {
            self::$instances[$a_type][$a_obj_id] = new self($a_obj_id, $a_type);
        }

        return self::$instances[$a_type][$a_obj_id];
    }

    public function setObjId(int $a_val) : void
    {
        $this->obj_id = $a_val;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @param array $a_val array of language codes
     */
    public function setLanguages(array $a_val) : void
    {
        $this->languages = $a_val;
    }

    /**
     * @return array array of language codes
     */
    public function getLanguages() : string
    {
        return $this->languages;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setType(string $type) : void
    {
        $this->type = $type;
    }
    
    public function getDefaultLanguage() : string
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
     * @param bool $a_force overwrite existing
     */
    public function addLanguage(
        string $a_lang,
        string $a_title,
        string $a_description,
        bool $a_default,
        bool $a_force = false
    ) : void {
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
     * @return string title of default language
     */
    public function getDefaultTitle() : string
    {
        foreach ($this->languages as $l) {
            if ($l["lang_default"]) {
                return $l["title"];
            }
        }
        return "";
    }

    /**
     * Set title for default language
     */
    public function setDefaultTitle(string $a_title) : void
    {
        foreach ($this->languages as $k => $l) {
            if ($l["lang_default"]) {
                $this->languages[$k]["title"] = $a_title;
            }
        }
    }

    /**
     * @return string description of default language
     */
    public function getDefaultDescription() : string
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
     */
    public function setDefaultDescription(string $a_description) : void
    {
        foreach ($this->languages as $k => $l) {
            if ($l["lang_default"]) {
                $this->languages[$k]["description"] = $a_description;
            }
        }
    }


    /**
     * @param string $a_lang language code
     */
    public function removeLanguage(string $a_lang) : void
    {
        if ($a_lang != $this->getDefaultLanguage()) {
            unset($this->languages[$a_lang]);
        }
    }

    public function read() : void
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

    public function delete() : void
    {
        $this->db->manipulate(
            "DELETE FROM il_translations " .
            " WHERE id = " . $this->db->quote($this->getObjId(), "integer") .
            " AND id_type = " . $this->db->quote($this->getType(), "text")
        );
    }

    public function save() : void
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
     * @throws ilObjectException
     */
    public function copy(int $a_obj_id) : self
    {
        $target_ml = new self($a_obj_id, $this->getType());
        $target_ml->setLanguages($this->getLanguages());
        $target_ml->save();
        return $target_ml;
    }


    
    /**
     * Export to xml
     */
    public function toXml(
        ilXmlWriter $writer
    ) : ilXmlWriter {
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
     * @param SimpleXMLElement $root
     */
    public function fromXML(SimpleXMLElement $root)
    {
        if ($root->translations) {
            $root = $root->translations;
        }
        
        foreach ($root->translation as $trans) {
            $this->addLanguage(
                trim($trans["language"]),
                trim($trans->title),
                trim($trans->description),
                (int) $trans["default"] != 0
            );
        }
    }
}
