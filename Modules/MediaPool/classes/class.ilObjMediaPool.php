<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";
require_once "./Services/MetaData/classes/class.ilMDLanguageItem.php";
require_once("./Modules/Folder/classes/class.ilObjFolder.php");
require_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");

/** @defgroup ModulesMediaPool Modules/MediaPool
 */

/**
* Media pool object
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ingroup ModulesMediaPool
*/
class ilObjMediaPool extends ilObject
{
    protected $mep_tree;
    public $for_translation = 0;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        // this also calls read() method! (if $a_id is set)
        $this->type = "mep";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * Set default width
    *
    * @param	int		default width
    */
    public function setDefaultWidth($a_val)
    {
        $this->default_width = $a_val;
    }
    
    /**
    * Get default width
    *
    * @return	int		default width
    */
    public function getDefaultWidth()
    {
        return $this->default_width;
    }

    /**
    * Set default height
    *
    * @param	int		default height
    */
    public function setDefaultHeight($a_val)
    {
        $this->default_height = $a_val;
    }
    
    /**
    * Get default height
    *
    * @return	int		default height
    */
    public function getDefaultHeight()
    {
        return $this->default_height;
    }

    /**
     * Set for translation
     *
     * @param bool $a_val lm has been imported for translation purposes
     */
    public function setForTranslation($a_val)
    {
        $this->for_translation = $a_val;
    }

    /**
     * Get for translation
     *
     * @return bool lm has been imported for translation purposes
     */
    public function getForTranslation()
    {
        return $this->for_translation;
    }

    /**
    * Read pool data
    */
    public function read()
    {
        $ilDB = $this->db;
        
        parent::read();

        $set = $ilDB->query(
            "SELECT * FROM mep_data " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setDefaultWidth($rec["default_width"]);
            $this->setDefaultHeight($rec["default_height"]);
            $this->setForTranslation($rec["for_translation"]);
        }
        $this->mep_tree = ilObjMediaPool::_getPoolTree($this->getId());
    }


    /**
    * Get Pool Tree
    *
    * @param	int		Media pool ID
    *
    * @return	object	Tree object of media pool
    */
    public static function _getPoolTree($a_obj_id)
    {
        $tree = new ilTree($a_obj_id);
        $tree->setTreeTablePK("mep_id");
        $tree->setTableNames("mep_tree", "mep_item");
        
        return $tree;
    }
    
    /**
     * Get pool tree
     *
     * @return object
     */
    public function getPoolTree()
    {
        return self::_getPoolTree($this->getId());
    }
    
    
    /**
    * create new media pool
    */
    public function create()
    {
        $ilDB = $this->db;
        
        parent::create();

        $ilDB->manipulate("INSERT INTO mep_data " .
            "(id, default_width, default_height, for_translation) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . ", " .
            $ilDB->quote($this->getDefaultWidth(), "integer") . ", " .
            $ilDB->quote($this->getDefaultHeight(), "integer") . ", " .
            $ilDB->quote($this->getForTranslation(), "integer") .
            ")");

        $this->createMepTree();
    }

    /**
     * Create media pool tree
     *
     * @param
     * @return
     */
    public function createMepTree()
    {
        // create media pool tree
        $this->mep_tree = new ilTree($this->getId());
        $this->mep_tree->setTreeTablePK("mep_id");
        $this->mep_tree->setTableNames('mep_tree', 'mep_item');
        $this->mep_tree->addTree($this->getId(), 1);
    }
    
    
    /**
    * get media pool folder tree
    */
    public function &getTree()
    {
        return $this->mep_tree;
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
    {
        $ilDB = $this->db;
        
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        $ilDB->manipulate(
            "UPDATE mep_data SET " .
            " default_width = " . $ilDB->quote($this->getDefaultWidth(), "integer") . "," .
            " default_height = " . $ilDB->quote($this->getDefaultHeight(), "integer") . "," .
            " for_translation = " . $ilDB->quote($this->getForTranslation(), "integer") . " " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );

        return true;
    }


    /**
    * delete object and all related data
    *
    * this method has been tested on may 9th 2004
    * media pool tree, media objects and folders
    * have been deleted correctly as desired
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // get childs
        $childs = $this->mep_tree->getSubTree($this->mep_tree->getNodeData($this->mep_tree->readRootId()));

        // delete tree
        $this->mep_tree->removeTree($this->mep_tree->getTreeId());

        // delete childs
        foreach ($childs as $child) {
            $fid = ilMediaPoolItem::lookupForeignId($child["obj_id"]);
            switch ($child["type"]) {
                case "mob":
                    if (ilObject::_lookupType($fid) == "mob") {
                        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                        $mob = new ilObjMediaObject($fid);
                        $mob->delete();
                    }
                    break;
            }
        }
        
        return true;
    }

    /**
    * get childs of node
    */
    public function getChilds($obj_id = "", $a_type = "")
    {
        $objs = array();
        $mobs = array();
        $pgs = array();
        if ($obj_id == "") {
            $obj_id = $this->mep_tree->getRootId();
        }

        if ($a_type == "fold" || $a_type == "") {
            $objs = $this->mep_tree->getChildsByType($obj_id, "fold");
        }
        if ($a_type == "mob" || $a_type == "") {
            $mobs = $this->mep_tree->getChildsByType($obj_id, "mob");
        }
        foreach ($mobs as $key => $mob) {
            $objs[] = $mob;
        }
        if ($a_type == "pg" || $a_type == "") {
            $pgs = $this->mep_tree->getChildsByType($obj_id, "pg");
        }
        foreach ($pgs as $key => $pg) {
            $objs[] = $pg;
        }

        return $objs;
    }

    /**
    * get childs of node
    */
    public function getChildsExceptFolders($obj_id = "")
    {
        if ($obj_id == "") {
            $obj_id = $this->mep_tree->getRootId();
        }

        $objs = $this->mep_tree->getFilteredChilds(array("fold", "dummy"), $obj_id);
        return $objs;
    }

    /**
    * Get media objects
    */
    public function getMediaObjects($a_title_filter = "", $a_format_filter = "", $a_keyword_filter = '', $a_caption_filter)
    {
        $ilDB = $this->db;

        $query = "SELECT DISTINCT mep_tree.*, object_data.* " .
            "FROM mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) " .
            " JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) ";
            
        if ($a_format_filter != "" or $a_caption_filter != '') {
            $query .= " JOIN media_item ON (media_item.mob_id = object_data.obj_id) ";
        }
            
        $query .=
            " WHERE mep_tree.mep_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND object_data.type = " . $ilDB->quote("mob", "text");
            
        // filter
        if (trim($a_title_filter) != "") {	// title
            $query .= " AND " . $ilDB->like("object_data.title", "text", "%" . trim($a_title_filter) . "%");
        }
        if ($a_format_filter != "") {			// format
            $filter = ($a_format_filter == "unknown")
                ? ""
                : $a_format_filter;
            $query .= " AND " . $ilDB->equals("media_item.format", $filter, "text", true);
        }
        if (trim($a_caption_filter)) {
            $query .= 'AND ' . $ilDB->like('media_item.caption', 'text', '%' . trim($a_caption_filter) . '%');
        }
            
        $query .=
            " ORDER BY object_data.title";
        
        $objs = array();
        $set = $ilDB->query($query);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec["foreign_id"] = $rec["obj_id"];
            $rec["obj_id"] = "";
            $objs[] = $rec;
        }
        
        // Keyword filter
        if ($a_keyword_filter) {
            include_once './Services/MetaData/classes/class.ilMDKeyword.php';
            $res = ilMDKeyword::_searchKeywords($a_keyword_filter, 'mob', 0);
            
            foreach ($objs as $obj) {
                if (in_array($obj['foreign_id'], $res)) {
                    $filtered[] = $obj;
                }
            }
            return (array) $filtered;
        }
        return $objs;
    }


    /**
     * @param int $a_id of the media pool
     * @return array of obj_id's (int) of media objects
     */
    public static function getAllMobIds($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT foreign_id as id FROM " .
            " mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) " .
            " JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) " .
            " WHERE mep_tree.mep_id = " . $ilDB->quote($a_id, "integer") .
            " AND mep_item.type = " . $ilDB->quote("mob", "text") .
            " AND object_data.type = " . $ilDB->quote("mob", "text");
        $set = $ilDB->query($query);
        $ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = $rec["id"];
        }
        return $ids;
    }
    
    /**
    * Get used formats
    */
    public function getUsedFormats()
    {
        $ilDB = $this->db;
        $lng = $this->lng;

        $query = "SELECT DISTINCT media_item.format f FROM mep_tree " .
            " JOIN mep_item ON (mep_item.obj_id = mep_tree.child) " .
            " JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) " .
            " JOIN media_item ON (media_item.mob_id = object_data.obj_id) " .
            " WHERE mep_tree.mep_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND object_data.type = " . $ilDB->quote("mob", "text") .
            " ORDER BY f";
        $formats = array();
        $set = $ilDB->query($query);
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["f"] != "") {
                $formats[$rec["f"]] = $rec["f"];
            } else {
                $formats["unknown"] = $lng->txt("mep_unknown");
            }
        }
        
        return $formats;
    }
    
    public function getParentId($obj_id = "")
    {
        if ($obj_id == "") {
            return false;
        }
        if ($obj_id == $this->mep_tree->getRootId()) {
            return false;
        }

        return $this->mep_tree->getParentId($obj_id);
    }
    
    /**
     * Insert into tree
     * @param int 	$a_obj_id (mep_item obj_id)
     * @param int $a_parent
     */
    public function insertInTree($a_obj_id, $a_parent = "")
    {
        if (!$this->mep_tree->isInTree($a_obj_id)) {
            $parent = ($a_parent == "")
                ? $this->mep_tree->getRootId()
                : $a_parent;
            $this->mep_tree->insertNode($a_obj_id, $parent);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Delete a child of media tree
     * @param	int		mep_item id
     */
    public function deleteChild($obj_id)
    {
        $node_data = $this->mep_tree->getNodeData($obj_id);
        $subtree = $this->mep_tree->getSubtree($node_data);

        // delete tree
        if ($this->mep_tree->isInTree($obj_id)) {
            $this->mep_tree->deleteTree($node_data);
        }

        // delete objects
        foreach ($subtree as $node) {
            $fid = ilMediaPoolItem::lookupForeignId($node["child"]);
            if ($node["type"] == "mob") {
                if (ilObject::_lookupType($fid) == "mob") {
                    $obj = new ilObjMediaObject($fid);
                    $obj->delete();
                }
            }

            if ($node["type"] == "fold") {
                if ($fid > 0 && ilObject::_lookupType($fid) == "fold") {
                    $obj = new ilObjFolder($fid, false);
                    $obj->delete();
                }
            }

            if ($node["type"] == "pg") {
                include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
                if (ilPageObject::_exists("mep", $node["child"])) {
                    $pg = new ilMediaPoolPage($node["child"]);
                    $pg->delete();
                }
            }
            
            include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
            $item = new ilMediaPoolItem($node["child"]);
            $item->delete();
        }
    }
    
    /**
     * Check whether foreign id is in tree
     *
     * @param
     * @return
     */
    public static function isForeignIdInTree($a_pool_id, $a_foreign_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM mep_tree JOIN mep_item ON (child = obj_id) WHERE " .
            " foreign_id = " . $ilDB->quote($a_foreign_id, "integer") .
            " AND mep_id = " . $ilDB->quote($a_pool_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
    
    /**
    * Check wheter a mep item id is in the media pool
    */
    public static function isItemIdInTree($a_pool_id, $a_item_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT * FROM mep_tree WHERE child = " .
            $ilDB->quote($a_item_id, "integer") .
            " AND mep_id = " . $ilDB->quote($a_pool_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
    
    /**
     * Create a new folder
     *
     * @param
     * @return
     */
    public function createFolder($a_title, $a_parent = 0)
    {
        // perform save
        $mep_item = new ilMediaPoolItem();
        $mep_item->setTitle($a_title);
        $mep_item->setType("fold");
        $mep_item->create();
        if ($mep_item->getId() > 0) {
            $tree = $this->getTree();
            $parent = $a_parent > 0
                ? $a_parent
                : $tree->getRootId();
            $this->insertInTree($mep_item->getId(), $parent);
            return $mep_item->getId();
        }
        return false;
    }
    
    /**
     * Clone media pool
     *
     * @param int target ref_id
     * @param int copy id
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        
        $new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setDefaultWidth($this->getDefaultWidth());
        $new_obj->setDefaultHeight($this->getDefaultHeight());
        $new_obj->update();

        // copy content
        $this->copyTreeContent(
            $new_obj,
            $new_obj->getTree()->readRootId(),
            $this->getTree()->readRootId()
        );

        return $new_obj;
    }

    /**
     * Copy tree content
     *
     * @param
     * @return
     */
    public function copyTreeContent($a_new_obj, $a_target_parent, $a_source_parent)
    {
        include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
        include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        
        // get all chapters of root lm
        $nodes = $this->getTree()->getChilds($a_source_parent);
        foreach ($nodes as $node) {
            $item = new ilMediaPoolItem();
            $item->setType($node["type"]);
            switch ($node["type"]) {
                case "mob":
                    $mob_id = ilMediaPoolItem::lookupForeignId($node["child"]);
                    $mob = new ilObjMediaObject($mob_id);
                    $new_mob = $mob->duplicate();
                    $item->setForeignId($new_mob->getId());
                    $item->setTitle($new_mob->getTitle());
                    $item->create();
                    break;
                
                case "pg":
                    $item->setTitle($node["title"]);
                    $item->create();
                    $page = new ilMediaPoolPage($node["child"]);
                    $new_page = new ilMediaPoolPage();
                    $new_page->setParentId($a_new_obj->getId());
                    $new_page->setId($item->getId());
                    $new_page->create();
                    
                    // copy page
                    $page->copy($new_page->getId(), $new_page->getParentType(), $new_page->getParentId(), true);
                    break;
                    
                case "fold":
                    $item->setTitle($node["title"]);
                    $item->create();
                    break;
            }

            // insert item into tree
            $a_new_obj->insertInTree($item->getId(), $a_target_parent);
            
            // handle childs
            $this->copyTreeContent($a_new_obj, $item->getId(), $node["child"]);
        }
    }

    /**
     * Export
     *
     * @param
     */
    public function exportXML($a_mode = "")
    {
        if (in_array($a_mode, array("master", "masternomedia"))) {
            include_once("./Services/Export/classes/class.ilExport.php");
            $exp = new ilExport();
            $conf = $exp->getConfig("Modules/MediaPool");
            $conf->setMasterLanguageOnly(true, ($a_mode == "master"));
            $exp->exportObject($this->getType(), $this->getId(), "4.4.0");
        }
    }
}
