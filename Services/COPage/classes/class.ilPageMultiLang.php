<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Multi-language properties
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilPageMultiLang
{
    protected $db;
    protected $parent_type;
    protected $parent_id;
    protected $master_lang;
    protected $languages = array();
    protected $activated = false;
    
    /**
     * Constructor
     *
     * @param string $a_parent_type parent object type
     * @param int $a_parent_id parent object id
     * @throws ilCOPageException
     */
    public function __construct($a_parent_type, $a_parent_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $this->db = $ilDB;
        
        $this->setParentType($a_parent_type);
        $this->setParentId($a_parent_id);

        if ($this->getParentType() == "") {
            include_once("./Services/COPage/exceptions/class.ilCOPageException.php");
            throw new ilCOPageException("ilPageMultiLang: No parent type passed.");
        }
        
        if ($this->getParentId() <= 0) {
            include_once("./Services/COPage/exceptions/class.ilCOPageException.php");
            throw new ilCOPageException("ilPageMultiLang: No parent ID passed.");
        }
        
        $this->read();
    }
    
    /**
     * Set parent type
     *
     * @param string $a_val parent type
     */
    public function setParentType($a_val)
    {
        $this->parent_type = $a_val;
    }
    
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return $this->parent_type;
    }
    
    /**
     * Set parent id
     *
     * @param int $a_val parent id
     */
    public function setParentId($a_val)
    {
        $this->parent_id = $a_val;
    }
    
    /**
     * Get parent id
     *
     * @return int parent id
     */
    public function getParentId()
    {
        return $this->parent_id;
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
     */
    public function addLanguage($a_lang)
    {
        if ($a_lang != "" && !in_array($a_lang, $this->languages)) {
            $this->languages[] = $a_lang;
        }
    }
    
    
    /**
     * Set activated
     *
     * @param bool $a_val activated?
     */
    protected function setActivated($a_val)
    {
        $this->activated = $a_val;
    }
    
    /**
     * Get activated
     *
     * @return bool activated?
     */
    public function getActivated()
    {
        return $this->activated;
    }
    
    /**
     * Read
     */
    public function read()
    {
        $set = $this->db->query(
            "SELECT * FROM copg_multilang " .
            " WHERE parent_type = " . $this->db->quote($this->getParentType(), "text") .
            " AND parent_id = " . $this->db->quote($this->getParentId(), "integer")
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            $this->setMasterLanguage($rec["master_lang"]);
            $this->setActivated(true);
        } else {
            $this->setActivated(false);
        }

        $this->setLanguages(array());
        $set = $this->db->query(
            "SELECT * FROM copg_multilang_lang " .
            " WHERE parent_type = " . $this->db->quote($this->getParentType(), "text") .
            " AND parent_id = " . $this->db->quote($this->getParentId(), "integer")
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->addLanguage($rec["lang"]);
        }
    }

    /**
     * Delete
     */
    public function delete()
    {
        $this->db->manipulate(
            "DELETE FROM copg_multilang " .
            " WHERE parent_type = " . $this->db->quote($this->getParentType(), "text") .
            " AND parent_id = " . $this->db->quote($this->getParentId(), "integer")
        );
        $this->db->manipulate(
            "DELETE FROM copg_multilang_lang " .
            " WHERE parent_type = " . $this->db->quote($this->getParentType(), "text") .
            " AND parent_id = " . $this->db->quote($this->getParentId(), "integer")
        );
    }

    /**
     * Save
     */
    public function save()
    {
        $this->delete();

        $this->db->manipulate("INSERT INTO copg_multilang " .
            "(parent_type, parent_id, master_lang) VALUES (" .
            $this->db->quote($this->getParentType(), "text") . "," .
            $this->db->quote($this->getParentId(), "integer") . "," .
            $this->db->quote($this->getMasterLanguage(), "text") .
            ")");
        
        foreach ($this->getLanguages() as $lang) {
            $this->db->manipulate("INSERT INTO copg_multilang_lang " .
                "(parent_type, parent_id, lang) VALUES (" .
                $this->db->quote($this->getParentType(), "text") . "," .
                $this->db->quote($this->getParentId(), "integer") . "," .
                $this->db->quote($lang, "text") .
                ")");
        }
    }

    /**
     * Copy multilinguality settings
     *
     * @param string $a_target_parent_type parent object type
     * @param int $a_target_parent_id parent object id
     * @return ilPageMultiLang target multilang object
     */
    public function copy($a_target_parent_type, $a_target_parent_id)
    {
        if ($this->getActivated()) {
            $target_ml = new ilPageMultiLang($a_target_parent_type, $a_target_parent_id);
            $target_ml->setMasterLanguage($this->getMasterLanguage());
            $target_ml->setLanguages($this->getLanguages());
            $target_ml->save();
            return $target_ml;
        }

        return null;
    }


    /**
     * Get effective language for given language. This checks if
     * - multilinguality is activated and
     * - the given language is part of the available translations
     * If not a "-" is returned (master language).
     *
     * @param string $a_lang language
     * @return string effective language ("-" for master)
     */
    public function getEffectiveLang($a_lang)
    {
        if ($this->getActivated() &&
            in_array($a_lang, $this->getLanguages()) &&
            ilPageObject::_exists($this->getParentType(), $this->getParentId(), $a_lang)) {
            return $a_lang;
        }
        return "-";
    }
}
