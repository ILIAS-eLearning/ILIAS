<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesXHTMLPage Services/XHTMLPage
 */

/**
* XHTML Page class. Should be used to store XHTML pages created by tiny
* (e.g. for ategories).
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup	ServicesXHTMLPage
*/
class ilXHTMLPage
{
    /**
     * @var ilDB
     */
    protected $db;

    public $id = 0;
    public $content = "";

    /**
    * Constructor
    *
    * @param	int		$a_id		page ID
    */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }
    
    /**
    * Get page ID.
    *
    * @return	int		page ID
    */
    public function getId()
    {
        return $this->id;
    }
    
    /**
    * Set page ID.
    *
    * @param	int		$a_id		page ID
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
    * Get content of page.
    *
    * @return	string		page content
    */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
    * Set content of page.
    *
    * @param	string	$a_content		page content
    */
    public function setContent($a_content)
    {
        $this->content = $a_content;
    }

    /**
    * Read page data from database.
    */
    public function read()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT * FROM xhtml_page WHERE id = " .
            $ilDB->quote($this->getId(), "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setContent($rec["content"]);
        }
    }
    
    /**
    * Lookup Content
    */
    public static function _lookupContent($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT content FROM xhtml_page WHERE id = " .
            $ilDB->quote($a_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["content"];
        }
    }

    /**
    * Lookup Saved Content
    */
    public static function _lookupSavedContent($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT save_content FROM xhtml_page WHERE id = " .
            $ilDB->quote($a_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["save_content"];
        }
    }

    /**
    * Save the page.
    */
    public function save()
    {
        $ilDB = $this->db;
        
        if ($this->getId() > 0) {
            $old_content = ilXHTMLPage::_lookupContent($this->getId());
            $ilDB->update("xhtml_page", array(
                "content" => array("clob", $this->getContent()),
                "save_content" => array("clob", $old_content)
                ), array(
                "id" => array("integer", $this->getId())
                ));
        } else {
            $this->setId($ilDB->nextId("xhtml_page"));
            $ilDB->insert("xhtml_page", array(
                "id" => array("integer", $this->getId()),
                "content" => array("clob", $this->getContent())
                ));
        }
    }
    
    /**
    * Undo last change.
    */
    public function undo()
    {
        $ilDB = $this->db;
        
        if ($this->getId() > 0) {
            $content = ilXHTMLPage::_lookupContent($this->getId());
            $save_content = ilXHTMLPage::_lookupSavedContent($this->getId());
            $ilDB->update("xhtml_page", array(
                "content" => array("clob", $save_content),
                "save_content" => array("clob", $content)
                ), array(
                "id" => array("integer", $this->getId())
                ));
        }
    }

    /**
    * Clear page.
    */
    public function clear()
    {
        $ilDB = $this->db;
        
        if ($this->getId() > 0) {
            $this->setContent("");
            $this->save();
        }
    }
}
