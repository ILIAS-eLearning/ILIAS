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

/**
 * Multi-language properties
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageMultiLang
{
    protected ilDBInterface $db;
    protected string $parent_type;
    protected int $parent_id;
    protected string $master_lang;
    protected array $languages = array();
    protected bool $activated = false;
    
    /**
     * Constructor
     *
     * @param string $a_parent_type parent object type
     * @param int $a_parent_id parent object id
     * @throws ilCOPageException
     */
    public function __construct(
        string $a_parent_type,
        int $a_parent_id
    ) {
        global $DIC;

        $ilDB = $DIC->database();
        
        $this->db = $ilDB;
        
        $this->setParentType($a_parent_type);
        $this->setParentId($a_parent_id);

        if ($this->getParentType() == "") {
            throw new ilCOPageException("ilPageMultiLang: No parent type passed.");
        }
        
        if ($this->getParentId() <= 0) {
            throw new ilCOPageException("ilPageMultiLang: No parent ID passed.");
        }
        
        $this->read();
    }
    
    public function setParentType(string $a_val) : void
    {
        $this->parent_type = $a_val;
    }
    
    public function getParentType() : string
    {
        return $this->parent_type;
    }
    
    public function setParentId(int $a_val) : void
    {
        $this->parent_id = $a_val;
    }
    
    public function getParentId() : int
    {
        return $this->parent_id;
    }
    
    public function setMasterLanguage(string $a_val) : void
    {
        $this->master_lang = $a_val;
    }
    
    public function getMasterLanguage() : string
    {
        return $this->master_lang;
    }

    public function setLanguages(array $a_val) : void
    {
        $this->languages = $a_val;
    }
    
    public function getLanguages() : array
    {
        return $this->languages;
    }
    
    public function addLanguage(string $a_lang) : void
    {
        if ($a_lang != "" && !in_array($a_lang, $this->languages)) {
            $this->languages[] = $a_lang;
        }
    }
    
    protected function setActivated(bool $a_val) : void
    {
        $this->activated = $a_val;
    }
    
    public function getActivated() : bool
    {
        return $this->activated;
    }
    
    public function read() : void
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

    public function delete() : void
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

    public function save() : void
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
     * @return ?ilPageMultiLang target multilang object
     * @throws ilCOPageException
     */
    public function copy(
        string $a_target_parent_type,
        int $a_target_parent_id
    ) : ?ilPageMultiLang {
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
    public function getEffectiveLang(string $a_lang) : string
    {
        if ($this->getActivated() &&
            in_array($a_lang, $this->getLanguages()) &&
            ilPageObject::_exists($this->getParentType(), $this->getParentId(), $a_lang)) {
            return $a_lang;
        }
        return "-";
    }
}
