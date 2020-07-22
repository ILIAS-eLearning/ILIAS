<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");

/**
* Class ilLMObject
*
* Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMObject
{
    /**
     * @var ilObjUser
     */
    protected $user;

    public $lm_id;
    public $type;
    public $id;
    public $meta_data;
    public $data_record;		// assoc array of lm_data record
    public $content_object;
    public $title;
    public $short_title;
    public $description;
    public $active = true;
    protected static $data_records = array();

    /**
     * @var ilDB
     */
    protected $db;

    /**
    * @param	object		$a_content_obj		content object (digi book or learning module)
    */
    public function __construct($a_content_obj, $a_id = 0)
    {
        global $DIC;
        $this->user = $DIC->user();

        $this->db = $DIC->database();

        $this->id = $a_id;
        $this->setContentObject($a_content_obj);
        $this->setLMId($a_content_obj->getId());
        if ($a_id != 0) {
            $this->read();
        }
    }

    /**
     * Meta data update listener
     *
     * Important note: Do never call create() or update()
     * method of ilObject here. It would result in an
     * endless loop: update object -> update meta -> update
     * object -> ...
     * Use static _writeTitle() ... methods instead.
     *
     * @param string $a_element md element
     * @return boolean success
     */
    public function MDUpdateListener($a_element)
    {
        include_once 'Services/MetaData/classes/class.ilMD.php';

        switch ($a_element) {
            case 'General':

                // Update Title and description
                $md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
                $md_gen = $md->getGeneral();

                ilLMObject::_writeTitle($this->getId(), $md_gen->getTitle());

                foreach ($md_gen->getDescriptionIds() as $id) {
                    $md_des = $md_gen->getDescription($id);
                    //					ilLMObject::_writeDescription($this->getId(),$md_des->getDescription());
                    break;
                }
                break;
                
            case 'Educational':
                include_once("./Services/Object/classes/class.ilObjectLP.php");
                $obj_lp = ilObjectLP::getInstance($this->getLMId());
                if (in_array(
                    $obj_lp->getCurrentMode(),
                    array(ilLPObjSettings::LP_MODE_TLT, ilLPObjSettings::LP_MODE_COLLECTION_TLT)
                )) {
                    include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
                    ilLPStatusWrapper::_refreshStatus($this->getLMId());
                }
                break;
            
            default:
        }
        return true;
    }


    /**
    * lookup named identifier (ILIAS_NID)
    */
    public static function _lookupNID($a_lm_id, $a_lm_obj_id, $a_type)
    {
        include_once 'Services/MetaData/classes/class.ilMD.php';
        //echo "-".$a_lm_id."-".$a_lm_obj_id."-".$a_type."-";
        $md = new ilMD($a_lm_id, $a_lm_obj_id, $a_type);
        $md_gen = $md->getGeneral();
        if (is_object($md_gen)) {
            foreach ($md_gen->getIdentifierIds() as $id) {
                $md_id = $md_gen->getIdentifier($id);
                if ($md_id->getCatalog() == "ILIAS_NID") {
                    return $md_id->getEntry();
                }
            }
        }
        
        return false;
    }


    /**
    * create meta data entry
    */
    public function createMetaData()
    {
        include_once 'Services/MetaData/classes/class.ilMDCreator.php';

        $ilUser = $this->user;

        $md_creator = new ilMDCreator($this->getLMId(), $this->getId(), $this->getType());
        $md_creator->setTitle($this->getTitle());
        $md_creator->setTitleLanguage($ilUser->getPref('language'));
        $md_creator->setDescription($this->getDescription());
        $md_creator->setDescriptionLanguage($ilUser->getPref('language'));
        $md_creator->setKeywordLanguage($ilUser->getPref('language'));
        $md_creator->setLanguage($ilUser->getPref('language'));
        $md_creator->create();

        return true;
    }

    /**
    * update meta data entry
    */
    public function updateMetaData()
    {
        include_once("Services/MetaData/classes/class.ilMD.php");
        include_once("Services/MetaData/classes/class.ilMDGeneral.php");
        include_once("Services/MetaData/classes/class.ilMDDescription.php");

        $md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
        $md_gen = $md->getGeneral();
        $md_gen->setTitle($this->getTitle());

        // sets first description (maybe not appropriate)
        $md_des_ids = $md_gen->getDescriptionIds();
        if (count($md_des_ids) > 0) {
            $md_des = $md_gen->getDescription($md_des_ids[0]);
            //			$md_des->setDescription($this->getDescription());
            $md_des->update();
        }
        $md_gen->update();
    }


    /**
    * delete meta data entry
    */
    public function deleteMetaData()
    {
        // Delete meta data
        include_once('Services/MetaData/classes/class.ilMD.php');
        $md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
        $md->deleteAll();
    }



    /**
    * this method should only be called by class ilLMObjectFactory
    */
    public function setDataRecord($a_record)
    {
        $this->data_record = $a_record;
    }

    public function read()
    {
        $ilDB = $this->db;

        if (!isset($this->data_record)) {
            $query = "SELECT * FROM lm_data WHERE obj_id = " .
                $ilDB->quote($this->id, "integer");
            $obj_set = $ilDB->query($query);
            $this->data_record = $ilDB->fetchAssoc($obj_set);
        }

        $this->type = $this->data_record["type"];
        $this->setImportId($this->data_record["import_id"]);
        $this->setTitle($this->data_record["title"]);
        $this->setShortTitle($this->data_record["short_title"]);
        $this->setLayout($this->data_record["layout"]);
    }


    /**
     * Preload data records by lm
     *
     * @param integer $a_lm_id lm id
     * @return int number of preloaded records
     */
    public static function preloadDataByLM($a_lm_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM lm_data " .
            " WHERE lm_id = " . $ilDB->quote($a_lm_id, "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            self::$data_records[$rec["obj_id"]] = $rec;
        }
        return count(self::$data_records);
    }


    /**
     * set title of lm object
     *
     * @param	string		$a_title	title of chapter or page
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * get title of lm object
     *
     * @return	string		title of chapter or page
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set short title of lm object
     *
     * @param	string		$a_title	short title of chapter or page
     */
    public function setShortTitle($a_title)
    {
        $this->short_title = $a_title;
    }

    /**
     * get short title of lm object
     *
     * @return	string		short title of chapter or page
     */
    public function getShortTitle()
    {
        return $this->short_title;
    }


    /**
     * Lookup title
     *
     * @param	int		lm object id
     */
    protected static function _lookup($a_obj_id, $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$data_records[$a_obj_id])) {
            return self::$data_records[$a_obj_id][$a_field];
        }

        $query = "SELECT " . $a_field . " FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec[$a_field];
    }

    /**
     * Lookup title
     *
     * @param int $a_obj_id object id
     * @return string
     */
    public static function _lookupTitle($a_obj_id)
    {
        return self::_lookup($a_obj_id, "title");
    }

    /**
     * Lookup short title
     *
     * @param int $a_obj_id object id
     * @return string
     */
    public static function _lookupShortTitle($a_obj_id)
    {
        return self::_lookup($a_obj_id, "short_title");
    }

    /**
    * Lookup type
    *
    * @param	int		id of pg st
    * @param	int		id of lm object [optional]
    */
    public static function _lookupType($a_obj_id, $a_lm_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$data_records[$a_obj_id])) {
            if ($a_lm_id == 0 || self::$data_records[$a_obj_id]["lm_id"] == $a_lm_id) {
                return self::$data_records[$a_obj_id]["type"];
            }
        }

        if ($a_lm_id) {
            $and = ' AND lm_id = ' . $ilDB->quote($a_lm_id, 'integer');
        }

        $query = "SELECT type FROM lm_data WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") . $and;
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["type"];
    }


    public static function _writeTitle($a_obj_id, $a_title)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE lm_data SET " .
            " title = " . $ilDB->quote($a_title, "text") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
        $ilDB->manipulate($query);
    }


    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setType($a_type)
    {
        $this->type = $a_type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setLMId($a_lm_id)
    {
        $this->lm_id = $a_lm_id;
    }

    public function getLMId()
    {
        return $this->lm_id;
    }

    public function setContentObject(&$a_content_obj)
    {
        $this->content_object = $a_content_obj;
    }

    public function &getContentObject()
    {
        return $this->content_object;
    }

    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getImportId()
    {
        return $this->import_id;
    }

    public function setImportId($a_id)
    {
        $this->import_id = $a_id;
    }

    /**
    * Set layout
    *
    * @param	string	layout
    */
    public function setLayout($a_val)
    {
        $this->layout = $a_val;
    }
    
    /**
    * Get layout
    *
    * @return	string	layout
    */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
    * write import id to db (static)
    *
    * @param	int		$a_id				lm object id
    * @param	string	$a_import_id		import id
    * @access	public
    */
    public static function _writeImportId($a_id, $a_import_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE lm_data " .
            "SET " .
            "import_id = " . $ilDB->quote($a_import_id, "text") . "," .
            "last_update = " . $ilDB->now() . " " .
            "WHERE obj_id = " . $ilDB->quote($a_id, "integer");

        $ilDB->manipulate($q);
    }

    public function create($a_upload = false)
    {
        $ilDB = $this->db;

        // insert object data
        $this->setId($ilDB->nextId("lm_data"));
        $query = "INSERT INTO lm_data (obj_id, title, type, layout, lm_id, import_id, short_title, create_date) " .
            "VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getType(), "text") . ", " .
            $ilDB->quote($this->getLayout(), "text") . ", " .
            $ilDB->quote($this->getLMId(), "integer") . "," .
            $ilDB->quote($this->getImportId(), "text") . "," .
            $ilDB->quote($this->getShortTitle(), "text") .
            ", " . $ilDB->now() . ")";
        $ilDB->manipulate($query);

        // create history entry
        include_once("./Services/History/classes/class.ilHistory.php");
        ilHistory::_createEntry(
            $this->getId(),
            "create",
            "",
            $this->content_object->getType() . ":" . $this->getType()
        );

        if (!$a_upload) {
            $this->createMetaData();
        }
    }

    /**
    * update complete object
    */
    public function update()
    {
        $ilDB = $this->db;

        $this->updateMetaData();

        $query = "UPDATE lm_data SET " .
            " lm_id = " . $ilDB->quote($this->getLMId(), "integer") .
            " ,title = " . $ilDB->quote($this->getTitle(), "text") .
            " ,short_title = " . $ilDB->quote($this->getShortTitle(), "text") .
            " ,layout = " . $ilDB->quote($this->getLayout(), "text") .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");

        $ilDB->manipulate($query);
    }


    /**
    * update public access flags in lm_data for all pages of a content object
    * @static
    * @access	public
    * @param	array	page ids
    * @param	integer	content object id
    * @return	of the jedi
    */
    public static function _writePublicAccessStatus($a_pages, $a_cont_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilLog = $DIC["ilLog"];
        $ilErr = $DIC["ilErr"];
        
        if (!is_array($a_pages)) {
            $a_pages = array(0);
            /*$message = sprintf('ilLMObject::_writePublicAccessStatus(): Invalid parameter! $a_pages must be an array');
            $ilLog->write($message,$ilLog->WARNING);
            $ilErr->raiseError($message,$ilErr->MESSAGE);
            return false;*/
        }
        
        if (empty($a_cont_obj_id)) {
            $message = sprintf('ilLMObject::_writePublicAccessStatus(): Invalid parameter! $a_cont_obj_id is empty');
            $ilLog->write($message, $ilLog->WARNING);
            $ilErr->raiseError($message, $ilErr->MESSAGE);
            return false;
        }
        
        // update structure entries: if at least one page of a chapter is public set chapter to public too
        $lm_tree = new ilTree($a_cont_obj_id);
        $lm_tree->setTableNames('lm_tree', 'lm_data');
        $lm_tree->setTreeTablePK("lm_id");
        $lm_tree->readRootId();
        
        // get all st entries of cont_obj
        $q = "SELECT obj_id FROM lm_data " .
             "WHERE lm_id = " . $ilDB->quote($a_cont_obj_id, "integer") . " " .
             "AND type = 'st'";
        $r = $ilDB->query($q);
        
        // add chapters with a public page to a_pages
        while ($row = $ilDB->fetchAssoc($r)) {
            $childs = $lm_tree->getChilds($row["obj_id"]);
            
            foreach ($childs as $page) {
                if ($page["type"] == "pg" and in_array($page["obj_id"], $a_pages)) {
                    array_push($a_pages, $row["obj_id"]);
                    break;
                }
            }
        }
        
        // update public access status of all pages of cont_obj
        $q = "UPDATE lm_data SET " .
             "public_access = CASE " .
             "WHEN " . $ilDB->in("obj_id", $a_pages, false, "integer") . " " .
             "THEN " . $ilDB->quote("y", "text") .
             "ELSE " . $ilDB->quote("n", "text") .
             "END " .
             "WHERE lm_id = " . $ilDB->quote($a_cont_obj_id, "integer") . " " .
             "AND " . $ilDB->in("type", array("pg", "st"), false, "text");
        $ilDB->manipulate($q);
        
        return true;
    }
    
    public static function _isPagePublic($a_node_id, $a_check_public_mode = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilLog = $DIC["ilLog"];

        if (empty($a_node_id)) {
            $message = sprintf('ilLMObject::_isPagePublic(): Invalid parameter! $a_node_id is empty');
            $ilLog->write($message, $ilLog->WARNING);
            return false;
        }
        
        if ($a_check_public_mode === true) {
            $lm_id = ilLMObject::_lookupContObjId($a_node_id);

            $q = "SELECT public_access_mode FROM content_object WHERE id = " .
                $ilDB->quote($lm_id, "integer");
            $r = $ilDB->query($q);
            $row = $ilDB->fetchAssoc($r);
            
            if ($row["public_access_mode"] == "complete") {
                return true;
            }
        }

        $q = "SELECT public_access FROM lm_data WHERE obj_id=" .
            $ilDB->quote($a_node_id, "integer");
        $r = $ilDB->query($q);
        $row = $ilDB->fetchAssoc($r);
        
        return ilUtil::yn2tf($row["public_access"]);
    }

    /**
    * delete lm object data
    */
    public function delete($a_delete_meta_data = true)
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM lm_data WHERE obj_id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        $this->deleteMetaData();
    }

    /**
    * get current object id for import id (static)
    *
    * import ids can exist multiple times (if the same learning module
    * has been imported multiple times). we get the object id of
    * the last imported object, that is not in trash
    *
    * @param	int		$a_import_id		import id
    *
    * @return	int		id
    */
    public static function _getIdForImportId($a_import_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $q = "SELECT obj_id FROM lm_data WHERE import_id = " .
            $ilDB->quote($a_import_id, "text") . " " .
            " ORDER BY create_date DESC";
        $obj_set = $ilDB->query($q);
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            $lm_id = ilLMObject::_lookupContObjID($obj_rec["obj_id"]);

            // link only in learning module, that is not trashed
            include_once("./Services/Help/classes/class.ilObjHelpSettings.php");
            $ref_ids = ilObject::_getAllReferences($lm_id);	// will be 0 if import of lm is in progress (new import)
            if (count($ref_ids) == 0 || ilObject::_hasUntrashedReference($lm_id) ||
                ilObjHelpSettings::isHelpLM($lm_id)) {
                return $obj_rec["obj_id"];
            }
        }

        return 0;
    }

    /**
    * Get all items for an import ID
    *
    * (only for items notnot in trash)
    *
    * @param	int		$a_import_id		import id
    *
    * @return	int		id
    */
    public static function _getAllObjectsForImportId($a_import_id, $a_in_lm = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $where = ($a_in_lm > 0)
            ? " AND lm_id = " . $ilDB->quote($a_in_lm, "integer") . " "
            : "";
        
        $q = "SELECT * FROM lm_data WHERE import_id = " .
            $ilDB->quote($a_import_id, "text") . " " .
            $where .
            " ORDER BY create_date DESC";
        $obj_set = $ilDB->query($q);
        
        $items = array();
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            // check, whether lm is not trashed
            if (ilObject::_hasUntrashedReference($obj_rec["lm_id"])) {
                $items[] = $obj_rec;
            }
        }

        return $items;
    }

    /**
    * checks wether a lm content object with specified id exists or not
    *
    * @param	int		$id		id
    *
    * @return	boolean		true, if lm content object exists
    */
    public static function _exists($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        if (is_int(strpos($a_id, "_"))) {
            $a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
        }

        $q = "SELECT * FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($q);
        if ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * static
    */
    public static function getObjectList($lm_id, $type = "")
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $type_str = ($type != "")
            ? "AND type = " . $ilDB->quote($type, "text") . " "
            : "";

        $query = "SELECT * FROM lm_data " .
            "WHERE lm_id= " . $ilDB->quote($lm_id, "integer") . " " .
            $type_str . " " .
            "ORDER BY title";
        $obj_set = $ilDB->query($query);
        $obj_list = array();
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            $obj_list[] = array("obj_id" => $obj_rec["obj_id"],
                                "title" => $obj_rec["title"],
                                "import_id" => $obj_rec["import_id"],
                                "type" => $obj_rec["type"]);
        }
        return $obj_list;
    }


    /**
    * delete all objects of content object (digi book / learning module)
    */
    public static function _deleteAllObjectData(&$a_cobj)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM lm_data " .
            "WHERE lm_id= " . $ilDB->quote($a_cobj->getId(), "integer");
        $obj_set = $ilDB->query($query);

        require_once("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            $lm_obj = ilLMObjectFactory::getInstance($a_cobj, $obj_rec["obj_id"], false);

            if (is_object($lm_obj)) {
                $lm_obj->delete(true);
            }
        }

        return true;
    }

    /**
    * get learning module / digibook id for lm object
    */
    public static function _lookupContObjID($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$data_records[$a_id])) {
            return self::$data_records[$a_id]["lm_id"];
        }

        $query = "SELECT lm_id FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["lm_id"];
    }

    /**
    * put this object into content object tree
    */
    public static function putInTree($a_obj, $a_parent_id = "", $a_target_node_id = "")
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];
        
        $tree = new ilTree($a_obj->getContentObject()->getId());
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");

        // determine parent
        $parent_id = ($a_parent_id != "")
            ? $a_parent_id
            : $tree->getRootId();

        // determine target
        if ($a_target_node_id != "") {
            $target = $a_target_node_id;
        } else {
            // determine last child that serves as predecessor
            if ($a_obj->getType() == "st") {
                $s_types = array("st", "pg");
                $childs = $tree->getChildsByTypeFilter($parent_id, $s_types);
            } else {
                $s_types = "pg";
                $childs = $tree->getChildsByType($parent_id, $s_types);
            }

            if (count($childs) == 0) {
                $target = IL_FIRST_NODE;
            } else {
                $target = $childs[count($childs) - 1]["obj_id"];
            }
        }

        if ($tree->isInTree($parent_id) && !$tree->isInTree($a_obj->getId())) {
            $ilLog->write("LMObject::putInTree: insertNode, ID: " . $a_obj->getId() .
                "Parent ID: " . $parent_id . ", Target: " . $target);

            $tree->insertNode($a_obj->getId(), $parent_id, $target);
        }
    }

    /**
    * Get learningmodule tree
    *
    * @param	int		learning module object id
    *
    * @return	object		tree object
    */
    public static function getTree($a_cont_obj_id)
    {
        $tree = new ilTree($a_cont_obj_id);
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");
        $tree->readRootId();
        
        return $tree;
    }
    
    /**
    * Copy a set of chapters/pages into the clipboard
    */
    public static function clipboardCut($a_cont_obj_id, $a_ids)
    {
        $tree = ilLMObject::getTree($a_cont_obj_id);
        
        if (!is_array($a_ids)) {
            return false;
        } else {
            // get all "top" ids, i.e. remove ids, that have a selected parent
            foreach ($a_ids as $id) {
                $path = $tree->getPathId($id);
                $take = true;
                foreach ($path as $path_id) {
                    if ($path_id != $id && in_array($path_id, $a_ids)) {
                        $take = false;
                    }
                }
                if ($take) {
                    $cut_ids[] = $id;
                }
            }
        }
        
        ilLMObject::clipboardCopy($a_cont_obj_id, $cut_ids);
        
        // remove the objects from the tree
        // note: we are getting chapters which are *not* in the tree
        // we do not delete any pages/chapters here
        foreach ($cut_ids as $id) {
            $curnode = $tree->getNodeData($id);
            if ($tree->isInTree($id)) {
                $tree->deleteTree($curnode);
            }
        }
    }

    /**
    * Copy a set of chapters/pages into the clipboard
    */
    public static function clipboardCopy($a_cont_obj_id, $a_ids)
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        $tree = ilLMObject::getTree($a_cont_obj_id);
        
        $ilUser->clipboardDeleteObjectsOfType("pg");
        $ilUser->clipboardDeleteObjectsOfType("st");
        
        // put them into the clipboard
        $time = date("Y-m-d H:i:s", time());
        $order = 0;
        foreach ($a_ids as $id) {
            $curnode = array();
            if ($tree->isInTree($id)) {
                $curnode = $tree->getNodeData($id);
                $subnodes = $tree->getSubTree($curnode);
                foreach ($subnodes as $subnode) {
                    if ($subnode["child"] != $id) {
                        $ilUser->addObjectToClipboard(
                            $subnode["child"],
                            $subnode["type"],
                            $subnode["title"],
                            $subnode["parent"],
                            $time,
                            $subnode["lft"]
                        );
                    }
                }
            }
            $order = ($curnode["lft"] > 0)
                ? $curnode["lft"]
                : (int) ($order + 1);
            $ilUser->addObjectToClipboard(
                $id,
                ilLMObject::_lookupType($id),
                ilLMObject::_lookupTitle($id),
                0,
                $time,
                $order
            );
        }
    }
    
    /**
    * Paste item (tree) from clipboard to current lm
    */
    public static function pasteTree(
        $a_target_lm,
        $a_item_id,
        $a_parent_id,
        $a_target,
        $a_insert_time,
        &$a_copied_nodes,
        $a_as_copy = false,
        $a_source_lm = null
    ) {
        global $DIC;

        $ilUser = $DIC->user();
        $ilLog = $DIC["ilLog"];
        
        include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
        include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
        
        $item_lm_id = ilLMObject::_lookupContObjID($a_item_id);
        $item_type = ilLMObject::_lookupType($a_item_id);
        $lm_obj = ilObjectFactory::getInstanceByObjId($item_lm_id);
        if ($item_type == "st") {
            $item = new ilStructureObject($lm_obj, $a_item_id);
        } elseif ($item_type == "pg") {
            $item = new ilLMPageObject($lm_obj, $a_item_id);
        }

        $ilLog->write("Getting from clipboard type " . $item_type . ", " .
            "Item ID: " . $a_item_id . ", of original LM: " . $item_lm_id);

        if ($item_lm_id != $a_target_lm->getId() && !$a_as_copy) {
            // @todo: check whether st is NOT in tree
            
            // "move" metadata to new lm
            include_once("Services/MetaData/classes/class.ilMD.php");
            $md = new ilMD($item_lm_id, $item->getId(), $item->getType());
            $new_md = $md->cloneMD($a_target_lm->getId(), $item->getId(), $item->getType());
            
            // update lm object
            $item->setLMId($a_target_lm->getId());
            $item->setContentObject($a_target_lm);
            $item->update();
            
            // delete old meta data set
            $md->deleteAll();
            
            if ($item_type == "pg") {
                $page = $item->getPageObject();
                $page->buildDom();
                $page->setParentId($a_target_lm->getId());
                $page->update();
            }
        }

        if ($a_as_copy) {
            $target_item = $item->copy($a_target_lm);
            $a_copied_nodes[$item->getId()] = $target_item->getId();
        } else {
            $target_item = $item;
        }
        
        $ilLog->write("Putting into tree type " . $target_item->getType() .
            "Item ID: " . $target_item->getId() . ", Parent: " . $a_parent_id . ", " .
            "Target: " . $a_target . ", Item LM:" . $target_item->getContentObject()->getId());
        
        ilLMObject::putInTree($target_item, $a_parent_id, $a_target);
        
        if ($a_source_lm == null) {
            $childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);
        } else {
            $childs = $a_source_lm->lm_tree->getChilds($item->getId());
            foreach ($childs as $k => $child) {
                $childs[$k]["id"] = $childs[$k]["child"];
            }
        }

        foreach ($childs as $child) {
            ilLMObject::pasteTree(
                $a_target_lm,
                $child["id"],
                $target_item->getId(),
                IL_LAST_NODE,
                $a_insert_time,
                $a_copied_nodes,
                $a_as_copy,
                $a_source_lm
            );
        }
        
        return $target_item->getId();
        // @todo: write history (see pastePage)
    }

    /**
    * Save titles for lm objects
    *
    * @param	array		titles (key is ID, value is title)
    */
    public static function saveTitles($a_lm, $a_titles, $a_lang = "-")
    {
        include_once("./Modules/LearningModule/classes/class.ilLMObjTranslation.php");
        
        if ($a_lang == "") {
            $a_lang = "-";
        }
        if (is_array($a_titles)) {
            include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
            include_once("./Services/MetaData/classes/class.ilMD.php");
            foreach ($a_titles as $id => $title) {
                // see #20375
                $title = ilFormPropertyGUI::removeProhibitedCharacters($title);
                if ($a_lang == "-") {
                    $lmobj = ilLMObjectFactory::getInstance($a_lm, $id, false);
                    if (is_object($lmobj)) {
                        // Update Title and description
                        $md = new ilMD($a_lm->getId(), $id, $lmobj->getType());
                        $md_gen = $md->getGeneral();
                        if (is_object($md_gen)) {			// see bug #0015843
                            $md_gen->setTitle($title);
                            $md_gen->update();
                            $md->update();
                        }
                        ilLMObject::_writeTitle($id, $title);
                    }
                } else {
                    $lmobjtrans = new ilLMObjTranslation($id, $a_lang);
                    $lmobjtrans->setTitle($title);
                    $lmobjtrans->save();
                }
            }
        }
    }

    /**
    * Update internal links, after multiple pages have been copied
    */
    public static function updateInternalLinks($a_copied_nodes, $a_parent_type = "lm")
    {
        $all_fixes = array();
        foreach ($a_copied_nodes as $original_id => $copied_id) {
            $copied_type = ilLMObject::_lookupType($copied_id);
            $copy_lm = ilLMObject::_lookupContObjID($copied_id);
            
            if ($copied_type == "pg") {
                foreach (ilPageObject::lookupTranslations($a_parent_type, $copied_id) as $l) {
                    //
                    // 1. Outgoing links from the copied page.
                    //
                    //$targets = ilInternalLink::_getTargetsOfSource($a_parent_type.":pg", $copied_id);
                    include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
                    $tpg = new ilLMPage($copied_id, 0, $l);
                    $tpg->buildDom();
                    $il = $tpg->getInternalLinks();
                    $targets = array();
                    foreach ($il as $l) {
                        $targets[] = array("type" => ilInternalLink::_extractTypeOfTarget($l["Target"]),
                            "id" => (int) ilInternalLink::_extractObjIdOfTarget($l["Target"]),
                            "inst" => (int) ilInternalLink::_extractInstOfTarget($l["Target"]));
                    }
                    $fix = array();
                    foreach ($targets as $target) {
                        if (($target["inst"] == 0 || $target["inst"] = IL_INST_ID) &&
                            ($target["type"] == "pg" || $target["type"] == "st")) {
                            // first check, whether target is also within the copied set
                            if ($a_copied_nodes[$target["id"]] > 0) {
                                $fix[$target["id"]] = $a_copied_nodes[$target["id"]];
                            } else {
                                // now check, if a copy if the target is already in the same lm

                                // only if target is not already in the same lm!
                                $trg_lm = ilLMObject::_lookupContObjID($target["id"]);
                                if ($trg_lm != $copy_lm) {
                                    $lm_data = ilLMObject::_getAllObjectsForImportId("il__" . $target["type"] . "_" . $target["id"]);
                                    $found = false;

                                    foreach ($lm_data as $item) {
                                        if (!$found && ($item["lm_id"] == $copy_lm)) {
                                            $fix[$target["id"]] = $item["obj_id"];
                                            $found = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // outgoing links to be fixed
                    if (count($fix) > 0) {
                        //echo "<br>--".$copied_id;
                        //var_dump($fix);
                        $t = ilObject::_lookupType($copy_lm);
                        if (is_array($all_fixes[$t . ":" . $copied_id])) {
                            $all_fixes[$t . ":" . $copied_id] += $fix;
                        } else {
                            $all_fixes[$t . ":" . $copied_id] = $fix;
                        }
                    }
                }
            }
            
            if ($copied_type == "pg" ||
                $copied_type == "st") {
                
                //
                // 2. Incoming links to the original pages
                //
                // A->B			A2			(A+B currently copied)
                // A->C			B2
                // B->A
                // C->A			C2->A		(C already copied)
                $original_lm = ilLMObject::_lookupContObjID($original_id);
                $original_type = ilObject::_lookupType($original_lm);
                
                if ($original_lm != $copy_lm) {
                
                    // This gets sources that link to A+B (so we have C here)
                    // (this also does already the trick when instance map areas are given in C)
                    // int_link, where target_type, target_id, target_inst -> ok
                    $sources = ilInternalLink::_getSourcesOfTarget(
                        $copied_type,
                        $original_id,
                        0
                    );
                    
                    // mobs linking to $original_id
                    // map_area, where link_type, target -> ok
                    $mobs = ilMapArea::_getMobsForTarget("int", "il__" . $copied_type .
                        "_" . $original_id);
                    
                    // pages using these mobs
                    include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                    foreach ($mobs as $mob) {
                        // mob_usage, where id -> ok
                        // mep_item, where foreign_id, type -> ok
                        // mep_tree, where child -> already existed
                        // il_news_item, where mob_id -> ok
                        // map_area, where link_type, target -> aready existed
                        // media_item, where id -> already existed
                        // personal_clipboard, where item_id, type -> ok
                        $usages = ilObjMediaObject::lookupUsages($mob);
                        foreach ($usages as $usage) {
                            if ($usage["type"] == "lm:pg" | $usage["type"] == "lm:st") {
                                $sources[] = $usage;
                            }
                        }
                    }
                    $fix = array();
                    foreach ($sources as $source) {
                        $stype = explode(":", $source["type"]);
                        $source_type = $stype[1];
    
                        if ($source_type == "pg" || $source_type == "st") {
                            // first of all: source must be in original lm
                            $src_lm = ilLMObject::_lookupContObjID($source["id"]);
                            
                            if ($src_lm == $original_lm) {
                                // check, if a copy if the source is already in the same lm
                                // now we look for the latest copy of C in LM2
                                $lm_data = ilLMObject::_getAllObjectsForImportId(
                                    "il__" . $source_type . "_" . $source["id"],
                                    $copy_lm
                                );
                                $found = false;
                                foreach ($lm_data as $item) {
                                    if (!$found) {
                                        $fix[$item["obj_id"]][$original_id] = $copied_id;
                                        $found = true;
                                    }
                                }
                            }
                        }
                    }
                    // outgoing links to be fixed
                    if (count($fix) > 0) {
                        foreach ($fix as $page_id => $fix_array) {
                            $t = ilObject::_lookupType($copy_lm);
                            if (is_array($all_fixes[$t . ":" . $page_id])) {
                                $all_fixes[$t . ":" . $page_id] += $fix_array;
                            } else {
                                $all_fixes[$t . ":" . $page_id] = $fix_array;
                            }
                        }
                    }
                }
            }
        }
        
        foreach ($all_fixes as $pg => $fixes) {
            $pg = explode(":", $pg);
            include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
            foreach (ilPageObject::lookupTranslations($pg[0], $pg[1]) as $l) {
                $page = ilPageObjectFactory::getInstance($pg[0], $pg[1], 0, $l);
                if ($page->moveIntLinks($fixes)) {
                    $page->update(true, true);
                }
            }
        }
    }
    
    /**
    * Check for unique types (all pages or all chapters)
    */
    public static function uniqueTypesCheck($a_items)
    {
        $types = array();
        if (is_array($a_items)) {
            foreach ($a_items as $item) {
                $type = ilLMObject::_lookupType($item);
                $types[$type] = $type;
            }
        }

        if (count($types) > 1) {
            return false;
        }
        return true;
    }

    /**
    * Write layout setting
    *
    * @param	int		lm object id
    * @param	string	layout
    */
    public static function writeLayout($a_obj_id, $a_layout, $a_lm = null)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $t = ilLMObject::_lookupType($a_obj_id);
        
        if ($t == "pg") {
            $query = "UPDATE lm_data SET " .
                " layout = " . $ilDB->quote($a_layout, "text") .
                " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
            $ilDB->manipulate($query);
        } elseif ($t == "st" && is_object($a_lm)) {
            $node = $a_lm->getLMTree()->getNodeData($a_obj_id);
            $child_nodes = $a_lm->getLMTree()->getSubTree($node);
            if (is_array($child_nodes) && count($child_nodes) > 0) {
                foreach ($child_nodes as $c) {
                    if ($c["type"] == "pg") {
                        $query = "UPDATE lm_data SET " .
                            " layout = " . $ilDB->quote($a_layout, "text") .
                            " WHERE obj_id = " . $ilDB->quote($c["child"], "integer");
                        $ilDB->manipulate($query);
                    }
                }
            }
        }
    }
    
    /**
    * Lookup type
    *
    * @param	int		lm object id
    */
    public static function lookupLayout($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT layout FROM lm_data WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["layout"];
    }

    /**
     * Get pages of chapter
     *
     * @param
     * @return
     */
    public static function getPagesOfChapter($a_lm_id, $a_chap_id)
    {
        // update structure entries: if at least one page of a chapter is public set chapter to public too
        $lm_tree = new ilTree($a_lm_id);
        $lm_tree->setTableNames('lm_tree', 'lm_data');
        $lm_tree->setTreeTablePK("lm_id");
        $lm_tree->readRootId();
        
        $childs = $lm_tree->getChildsByType($a_chap_id, "pg");

        return $childs;
    }
    
    /**
     * Get all objects of learning module
     *
     * @param
     * @return
     */
    public static function _getAllLMObjectsOfLM($a_lm_id, $a_type = "")
    {
        global $DIC;

        $ilDB = $DIC->database();

        $and = ($a_type != "")
            ? " AND type = " . $ilDB->quote($a_type, "text")
            : "";

        $set = $ilDB->query("SELECT obj_id FROM lm_data " .
            " WHERE lm_id = " . $ilDB->quote($a_lm_id, "integer") . $and);
        $obj_ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $obj_ids[] = $rec["obj_id"];
        }

        return $obj_ids;
    }


    ////
    //// Export ID handling
    ////
    
    /**
     * Save export id
     *
     * @param
     * @return
     */
    public static function saveExportId($a_lm_id, $a_lmobj_id, $a_exp_id, $a_type = "pg")
    {
        global $DIC;

        $ilDB = $DIC->database();

        include_once("Services/MetaData/classes/class.ilMDIdentifier.php");

        if (trim($a_exp_id) == "") {
            // delete export ids, if existing
            $entries = ilMDIdentifier::_getEntriesForObj(
                $a_lm_id,
                $a_lmobj_id,
                $a_type
            );

            foreach ($entries as $id => $e) {
                if ($e["catalog"] == "ILIAS_NID") {
                    $identifier = new ilMDIdentifier();
                    $identifier->setMetaId($id);
                    $identifier->delete();
                }
            }
        } else {
            // update existing entry
            $entries = ilMDIdentifier::_getEntriesForObj(
                $a_lm_id,
                $a_lmobj_id,
                $a_type
            );

            $updated = false;
            foreach ($entries as $id => $e) {
                if ($e["catalog"] == "ILIAS_NID") {
                    $identifier = new ilMDIdentifier();
                    $identifier->setMetaId($id);
                    $identifier->read();
                    $identifier->setEntry($a_exp_id);
                    $identifier->update();
                    $updated = true;
                }
            }

            // nothing updated? create a new one
            if (!$updated) {
                include_once("./Services/MetaData/classes/class.ilMD.php");
                $md = new ilMD($a_lm_id, $a_lmobj_id, $a_type);
                $md_gen = $md->getGeneral();
                $identifier = $md_gen->addIdentifier();
                $identifier->setEntry($a_exp_id);
                $identifier->setCatalog("ILIAS_NID");
                $identifier->save();
            }
        }
    }

    /**
     * Get export ID
     *
     * @param
     * @return
     */
    public static function getExportId($a_lm_id, $a_lmobj_id, $a_type = "pg")
    {
        // look for export id
        include_once("./Services/MetaData/classes/class.ilMDIdentifier.php");
        $entries = ilMDIdentifier::_getEntriesForObj(
            $a_lm_id,
            $a_lmobj_id,
            $a_type
        );

        foreach ($entries as $e) {
            if ($e["catalog"] == "ILIAS_NID") {
                return $e["entry"];
            }
        }
    }

    /**
     * Does export ID exist in lm?
     *
     * @param
     * @return
     */
    public function existsExportID($a_lm_id, $a_exp_id, $a_type = "pg")
    {
        include_once("./Services/MetaData/classes/class.ilMDIdentifier.php");
        return ilMDIdentifier::existsIdInRbacObject($a_lm_id, $a_type, "ILIAS_NID", $a_exp_id);
    }

    /**
     * Get duplicate export IDs (count export ID usages)
     */
    public static function getDuplicateExportIDs($a_lm_id, $a_type = "pg")
    {
        include_once("./Services/MetaData/classes/class.ilMDIdentifier.php");
        $entries = ilMDIdentifier::_getEntriesForRbacObj($a_lm_id, $a_type);
        $res = array();
        foreach ($entries as $e) {
            if ($e["catalog"] == "ILIAS_NID") {
                if (ilLMObject::_exists($e["obj_id"])) {
                    $res[trim($e["entry"])]++;
                }
            }
        }
        return $res;
    }
    
    /**
     * Does export ID exist in lm?
     *
     * @param
     * @return
     */
    public function getExportIDInfo($a_lm_id, $a_exp_id, $a_type = "pg")
    {
        include_once("./Services/MetaData/classes/class.ilMDIdentifier.php");
        $data = ilMDIdentifier::readIdData($a_lm_id, $a_type, "ILIAS_NID", $a_exp_id);
        return $data;
    }

    /**
     * Get affective title
     *
     * @param
     * @return
     */
    public static function _getPresentationTitle(
        $a_node,
        $a_mode = IL_PAGE_TITLE,
        $a_include_numbers = false,
        $a_time_scheduled_activation = false,
        $a_force_content = false,
        $a_lm_id = 0,
        $a_lang = "-"
    ) {
        if ($a_lang == "") {
            $a_lang = "-";
        }
        
        if ($a_node["type"] == "st") {
            include_once './Modules/LearningModule/classes/class.ilStructureObject.php';
            return ilStructureObject::_getPresentationTitle(
                $a_node["child"],
                IL_CHAPTER_TITLE,
                $a_include_numbers,
                $a_time_scheduled_activation,
                $a_force_content,
                $a_lm_id,
                $a_lang
            );
        } else {
            include_once './Modules/LearningModule/classes/class.ilLMPageObject.php';
            return ilLMPageObject::_getPresentationTitle(
                $a_node["child"],
                $a_mode,
                $a_include_numbers,
                $a_time_scheduled_activation,
                $a_force_content,
                $a_lm_id,
                $a_lang
            );
        }
    }

    /**
     * Get short titles
     *
     * @param
     * @return array
     */
    public static function getShortTitles($a_lm_id, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();

        $title_data = array();
        if ($a_lang == "-") {
            $set = $db->query("SELECT t.child, d.obj_id, d.title, d.short_title FROM lm_data d LEFT JOIN lm_tree t ON (d.obj_id = t.child) WHERE d.lm_id = " .
                $db->quote($a_lm_id, "integer") . " ORDER BY t.lft, d.title");
        } else {
            $set = $db->query("SELECT t.child, d.obj_id, tr.title, tr.short_title, d.title default_title, d.short_title default_short_title FROM lm_data d " .
                " LEFT JOIN lm_tree t ON (d.obj_id = t.child) " .
                " LEFT JOIN lm_data_transl tr ON (tr.id = d.obj_id AND tr.lang=" . $db->quote($a_lang, "text") . ") WHERE d.lm_id = " .
                $db->quote($a_lm_id, "integer") . " ORDER BY t.lft, d.title");
        }
        while ($rec = $db->fetchAssoc($set)) {
            $title_data[] = $rec;
        }
        return $title_data;
    }

    /**
     * Write short title
     *
     * @param integer $a_id object id
     * @param string $a_short_title short title
     */
    public static function writeShortTitle($a_id, $a_short_title, $a_lang = "-")
    {
        global $DIC;

        $db = $DIC->database();

        if ($a_lang != "-" && $a_lang != "") {
            $trans = new ilLMObjTranslation($a_id, $a_lang);
            $trans->setShortTitle($a_short_title);
            $trans->save();
        } else {
            $db->manipulate(
                "UPDATE lm_data SET " .
                " short_title = " . $db->quote($a_short_title, "text") .
                " WHERE obj_id = " . $db->quote($a_id, "integer")
            );
        }
    }
}
