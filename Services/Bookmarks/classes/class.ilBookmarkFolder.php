<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* bookmark folder
* (note: this class handles personal bookmarks folders only)
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Manfred Thaler <manfred.thaler@endo7.com>
* @version $Id$
* @ingroup ServicesBookmarks
*/
class ilBookmarkFolder
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
    * tree
    * @var object
    * @access private
    */
    public $tree;
    public $id;
    public $title;
    public $parent;

    /**
    * Constructor
    * @access	public
    * @param	integer		user_id (optional)
    */
    public function __construct($a_bmf_id = 0, $a_tree_id = 0)
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
        $this->id = $a_bmf_id;

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
        $bmf_set = $ilDB->query($q);
        if ($ilDB->numRows($bmf_set) == 0) {
            $message = "ilBookmarkFolder::read(): Bookmark Folder with id " . $this->getId() . " not found!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        } else {
            $bmf = $ilDB->fetchAssoc($bmf_set);
            $this->setTitle($bmf["title"]);
            $this->setParent($this->tree->getParentId($this->getId()));
        }
    }

    /**
    * delete object data
    */
    public function delete()
    {
        $ilDB = $this->db;
        
        $q = "DELETE FROM bookmark_data WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->query($q);
    }

    /**
    * create personal bookmark tree
    */
    public function createNewBookmarkTree()
    {
        $ilDB = $this->db;

        /*
        $q = "INSERT INTO bookmark_data (user_id, title, target, type) ".
            "VALUES ('".$this->tree->getTreeId()."','dummy_folder','','bmf')";
        $ilDB->query($q);*/
        //$this->tree->addTree($this->tree->getTreeId(), $ilDB->getLastInsertId());
        $this->tree->addTree($this->tree->getTreeId(), 1);
    }

    /**
    * creates new bookmark folder in db
    *
    * note: parent and title must be set
    */
    public function create()
    {
        $ilDB = $this->db;
        
        $this->setId($ilDB->nextId("bookmark_data"));
        $q = sprintf(
            "INSERT INTO bookmark_data (obj_id, user_id, title, type) " .
                "VALUES (%s,%s,%s,%s)",
            $ilDB->quote($this->getId(), "integer"),
            $ilDB->quote($GLOBALS['DIC']['ilUser']->getId(), "integer"),
            $ilDB->quote($this->getTitle(), "text"),
            $ilDB->quote('bmf', "text")
        );

        $ilDB->manipulate($q);
        $this->tree->insertNode($this->getId(), $this->getParent());
    }
    
    /**
    * Update bookmark folder item
    */
    public function update()
    {
        $ilDB = $this->db;
        
        $q = sprintf(
            "UPDATE bookmark_data SET title=%s " .
                "WHERE obj_id=%s",
            $ilDB->quote($this->getTitle(), "text"),
            $ilDB->quote($this->getId(), "integer")
        );
        $ilDB->manipulate($q);
    }


    public function getId()
    {
        return $this->id;
    }

    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($a_parent_id)
    {
        $this->parent = $a_parent_id;
    }

    /**
    * lookup bookmark folder title
    */
    public static function _lookupTitle($a_bmf_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM bookmark_data WHERE obj_id = " .
            $ilDB->quote($a_bmf_id, "integer");
        $bmf_set = $ilDB->query($q);
        $bmf = $ilDB->fetchAssoc($bmf_set);

        return $bmf["title"];
    }

    /**
    * static
    */
    public static function getObjects($a_id)
    {
        $a_tree_id = $GLOBALS['DIC']['ilUser']->getId();
        $tree = new ilTree($a_tree_id);
        $tree->setTableNames('bookmark_tree', 'bookmark_data');

        if (empty($a_id)) {
            $a_id = $tree->getRootId();
        }

        $childs = $tree->getChilds($a_id, "title");

        $objects = array();
        $bookmarks = array();

        foreach ($childs as $key => $child) {
            switch ($child["type"]) {
                case "bmf":
                    $objects[] = $child;
                    break;

                case "bm":
                    $bookmarks[] = $child;
                    break;
            }
        }
        foreach ($bookmarks as $key => $bookmark) {
            $objects[] = $bookmark;
        }
        return $objects;
    }
    
    /**
    * Get number of folders and bookmarks for current user.
    */
    public static function _getNumberOfObjects()
    {
        $a_tree_id = $GLOBALS['DIC']['ilUser']->getId();
        $tree = new ilTree($a_tree_id);
        $tree->setTableNames('bookmark_tree', 'bookmark_data');

        $root_node = $tree->getNodeData($tree->getRootId());
        
        if ($root_node["lft"] != "") {
            $bmf = $tree->getSubTree($root_node, false, "bmf");
            $bm = $tree->getSubTree($root_node, false, "bm");
        } else {
            $bmf = array("dummy");
            $bm = array();
        }
        
        return array("folders" => (int) count($bmf) - 1, "bookmarks" => (int) count($bm));
    }

    
    /**
    * static
    */
    public static function getObject($a_id)
    {
        $a_tree_id = $GLOBALS['DIC']['ilUser']->getId();
        $tree = new ilTree($a_tree_id);
        $tree->setTableNames('bookmark_tree', 'bookmark_data');

        if (empty($a_id)) {
            $a_id = $tree->getRootId();
        }

        $object = $tree->getNodeData($a_id);
        return $object;
    }

    public static function isRootFolder($a_id)
    {
        $a_tree_id = $GLOBALS['DIC']['ilUser']->getId();
        $tree = new ilTree($a_tree_id);
        $tree->setTableNames('bookmark_tree', 'bookmark_data');

        if ($a_id == $tree->getRootId()) {
            return true;
        } else {
            return false;
        }
    }

    public function getRootFolder()
    {
        $a_tree_id = $GLOBALS['DIC']['ilUser']->getId();
        $tree = new ilTree($a_tree_id);
        $tree->setTableNames('bookmark_tree', 'bookmark_data');

        return $tree->getRootId();
    }

    public static function _getParentId($a_id)
    {
        $a_tree_id = $GLOBALS['DIC']['ilUser']->getId();
        $tree = new ilTree($a_tree_id);
        $tree->setTableNames('bookmark_tree', 'bookmark_data');
        return $tree->getParentId($a_id);
    }
}
