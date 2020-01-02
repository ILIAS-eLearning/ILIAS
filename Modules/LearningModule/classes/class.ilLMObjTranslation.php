<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Translation information on lm object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMObjTranslation
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $lang;
    protected $title;
    protected $short_title;
    protected $create_date;
    protected $last_update;

    /**
     * Constructor
     *
     * @param int $a_id object id (page, chapter)
     * @param string $a_lang language code
     */
    public function __construct($a_id = 0, $a_lang = "")
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0 && $a_lang != "") {
            $this->setId($a_id);
            $this->setLang($a_lang);
            $this->read();
        }
    }
    
    /**
     * Set Id
     *
     * @param int $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
     * Get Id
     *
     * @return int id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set lang
     *
     * @param string $a_val language
     */
    public function setLang($a_val)
    {
        $this->lang = $a_val;
    }
    
    /**
     * Get lang
     *
     * @return string language
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set title
     *
     * @param string $a_val title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }

    /**
     * Get title
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set short title
     *
     * @param string $a_val short title
     */
    public function setShortTitle($a_val)
    {
        $this->short_title = $a_val;
    }

    /**
     * Get short title
     *
     * @return string short title
     */
    public function getShortTitle()
    {
        return $this->short_title;
    }

    /**
     * Get create date
     *
     * @return string create date
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Get update date
     *
     * @return string update date
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }
    
    /**
     * Read
     */
    public function read()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM lm_data_transl " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer") .
            " AND lang = " . $ilDB->quote($this->getLang(), "text")
        );
        $rec  = $ilDB->fetchAssoc($set);
        $this->setTitle($rec["title"]);
        $this->setShortTitle($rec["short_title"]);
        $this->create_date = $rec["create_date"];
        $this->last_update = $rec["last_update"];
    }
    
    /**
     * Save (inserts if not existing, otherwise updates)
     */
    public function save()
    {
        $ilDB = $this->db;
        
        if (!self::exists($this->getId(), $this->getLang())) {
            $ilDB->manipulate("INSERT INTO lm_data_transl " .
                "(id, lang, title, short_title, create_date, last_update) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($this->getLang(), "text") . "," .
                $ilDB->quote($this->getTitle(), "text") . "," .
                $ilDB->quote($this->getShortTitle(), "text") . "," .
                $ilDB->now() . "," .
                $ilDB->now() .
                ")");
        } else {
            $ilDB->manipulate(
                "UPDATE lm_data_transl SET " .
                " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
                " short_title = " . $ilDB->quote($this->getShortTitle(), "text") . "," .
                " last_update = " . $ilDB->now() .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer") .
                " AND lang = " . $ilDB->quote($this->getLang(), "text")
            );
        }
    }

    /**
     * Check for existence
     *
     * @param int $a_id object id (page, chapter)
     * @param string $a_lang language code
     * @return bool true/false
     */
    public static function exists($a_id, $a_lang)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM lm_data_transl " .
            " WHERE id = " . $ilDB->quote($a_id, "integer") .
            " AND lang = " . $ilDB->quote($a_lang, "text")
        );
        if ($rec  = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Copy all translations of an object
     *
     * @param int $a_source_id source id
     * @param int $a_target_id target
     */
    public static function copy($a_source_id, $a_target_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM lm_data_transl " .
            " WHERE id = " . $ilDB->quote($a_source_id, "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $lmobjtrans = new ilLMObjTranslation($a_target_id, $rec["lang"]);
            $lmobjtrans->setTitle($rec["title"]);
            $lmobjtrans->setShortTitle($rec["short_title"]);
            $lmobjtrans->save();
        }
    }
}
