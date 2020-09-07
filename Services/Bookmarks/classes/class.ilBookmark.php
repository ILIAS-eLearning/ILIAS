<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesBookmarks Services/Bookmarks
 */

/**
 * Class Bookmarks
 * Bookmark management
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Manfred Thaler <manfred.thaler@endo7.com>
 * @version $Id$
 * @ingroup ServicesBookmarks
 */
class ilBookmark
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
    * User Id
    * @var integer
    * @access public
    */
    public $user_Id;

    /**
    * ilias object
    * @var object ilias
    * @access public
    */
    public $tree;

    public $title;
    public $description;
    public $target;
    public $id;
    public $parent;

    /**
    * Constructor
    * @access	public
    * @param	integer		user_id (optional)
    */
    public function __construct($a_bm_id = 0, $a_tree_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->error = $DIC["ilErr"];
        // Initiate variables
        if ($a_tree_id == 0) {
            $a_tree_id = $GLOBALS['DIC']['ilUser']->getId();
        }

        $this->tree = new ilTree($a_tree_id);
        $this->tree->setTableNames('bookmark_tree', 'bookmark_data');

        $this->id = $a_bm_id;

        if (!empty($this->id)) {
            $this->read();
        }
    }


    /**
    * read bookmark folder data from db
    */
    public function read()
    {
        $ilDB = $this->db;
        $ilErr = $this->error;

        $q = "SELECT * FROM bookmark_data WHERE obj_id = " .
            $ilDB->quote($this->getId(), "integer");
        $bm_set = $ilDB->query($q);
        if ($ilDB->numRows($bm_set) == 0) {
            $message = "ilBookmark::read(): Bookmark with id " . $this->id . " not found!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        } else {
            $bm = $ilDB->fetchAssoc($bm_set);
            $this->setTitle($bm["title"]);
            $this->setDescription($bm["description"]);
            $this->setTarget($bm["target"]);
            $this->setParent($this->tree->getParentId($this->id));
        }
    }

    /**
    * Delete bookmark data
    */
    public function delete()
    {
        $ilDB = $this->db;

        if ($this->getId() != 1) {
            $q = "DELETE FROM bookmark_data WHERE obj_id = " .
                $ilDB->quote($this->getId(), "integer");
            $ilDB->manipulate($q);
        }
    }


    /**
    * Create new bookmark item
    */
    public function create()
    {
        $ilDB = $this->db;
        
        $this->setId($ilDB->nextId("bookmark_data"));
        $q = sprintf(
            "INSERT INTO bookmark_data (obj_id, user_id, title,description, target, type) " .
                "VALUES (%s,%s,%s,%s,%s,%s)",
            $ilDB->quote($this->getId(), "integer"),
            $ilDB->quote($GLOBALS['DIC']['ilUser']->getId(), "integer"),
            $ilDB->quote($this->getTitle(), "text"),
            $ilDB->quote($this->getDescription(), "text"),
            $ilDB->quote($this->getTarget(), "text"),
            $ilDB->quote('bm', "text")
        );

        $ilDB->manipulate($q);
        $this->tree->insertNode($this->getId(), $this->getParent());
    }

    /**
    * Update bookmark item
    */
    public function update()
    {
        $ilDB = $this->db;
        
        $q = sprintf(
            "UPDATE bookmark_data SET title=%s,description=%s,target=%s " .
                "WHERE obj_id=%s",
            $ilDB->quote($this->getTitle(), "text"),
            $ilDB->quote($this->getDescription(), "text"),
            $ilDB->quote($this->getTarget(), "text"),
            $ilDB->quote($this->getId(), "integer")
        );
        $ilDB->manipulate($q);
    }


    /*
    * set id
    * @access	public
    * @param	integer
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
    * set title
    * @access	public
    * @param	string
    */
    public function setTitle($a_str)
    {
        $this->title = $a_str;
    }

    public function getTitle()
    {
        return $this->title;
    }
    /**
    * set description
    * @access	public
    * @param	string
    */
    public function setDescription($a_str)
    {
        $this->description = $a_str;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
    * set target
    * @access	public
    * @param	string
    */
    public function setTarget($a_target)
    {
        $this->target = $a_target;
    }


    public function getTarget()
    {
        return $this->target;
    }

    public function setParent($a_parent_id)
    {
        $this->parent = $a_parent_id;
    }

    public function getParent()
    {
        return $this->parent;
    }
    
    /**
    * get type of a given id
    * @param number id
    */
    public static function _getTypeOfId($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM bookmark_data WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $bm_set = $ilDB->query($q);
        if ($ilDB->numRows($bm_set) == 0) {
            return null;
        } else {
            $bm = $ilDB->fetchAssoc($bm_set);
            return $bm["type"];
        }
    }
}
