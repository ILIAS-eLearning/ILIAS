<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTree.php");

/**
 * A node in the skill tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillTreeNode
{
    /**
     * @var ilDB
     */
    protected $db;

    const STATUS_PUBLISH = 0;
    const STATUS_DRAFT = 1;
    const STATUS_OUTDATED = 2;
    public $type;
    public $id;
    public $title;

    /**
    * @param	int		node id
    */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $a_id;
        
        $this->skill_tree = new ilSkillTree();

        if ($a_id != 0) {
            $this->read();
        }
    }

    /**
     * Set title
     *
     * @param	string		$a_title	title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
     * Get title
     *
     * @return	string		title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type
     *
     * @param	string		Type
     */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
     * Get type
     *
     * @return	string		Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set Node ID
     *
     * @param	int		Node ID
     */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
     * Get Node ID
     *
     * @param	int		Node ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set self evaluation
     *
     * @param	boolean	self evaluation
     */
    public function setSelfEvaluation($a_val)
    {
        $this->self_eval = $a_val;
    }

    /**
     * Get self evaluation
     *
     * @return	boolean	self evaluation
     */
    public function getSelfEvaluation()
    {
        return $this->self_eval;
    }
    
    /**
     * Set order nr
     *
     * @param int $a_val order nr
     */
    public function setOrderNr($a_val)
    {
        $this->order_nr = $a_val;
    }
    
    /**
     * Get order nr
     *
     * @return int order nr
     */
    public function getOrderNr()
    {
        return $this->order_nr;
    }

    /**
     * Set import id
     *
     * @param string $a_val import id
     */
    public function setImportId($a_val)
    {
        $this->import_id = $a_val;
    }

    /**
     * Get import id
     *
     * @return string import id
     */
    public function getImportId()
    {
        return $this->import_id;
    }

    /**
     * Set creation date
     *
     * @param string $a_val creation date
     */
    protected function setCreationDate($a_val)
    {
        $this->creation_date = $a_val;
    }

    /**
     * Get creation date
     *
     * @return string creation date
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * Get all status
     *
     * @return array array of status, key is value, value is lang text
     */
    public static function getAllStatus()
    {
        global $DIC;

        $lng = $DIC->language();

        return array(
            self::STATUS_DRAFT => $lng->txt("skmg_status_draft"),
            self::STATUS_PUBLISH => $lng->txt("skmg_status_publish"),
            self::STATUS_OUTDATED => $lng->txt("skmg_status_outdated")
        );
    }

    /**
     * Get status info
     *
     * @param int $a_status status
     * @return string info text
     */
    public static function getStatusInfo($a_status)
    {
        global $DIC;

        $lng = $DIC->language();

        switch ($a_status) {
            case self::STATUS_PUBLISH: return $lng->txt("skmg_status_publish_info");
            case self::STATUS_DRAFT: return $lng->txt("skmg_status_draft_info");
            case self::STATUS_OUTDATED: return $lng->txt("skmg_status_outdated_info");
        }
        return "";
    }

    /**
    * Read Data of Node
    */
    public function read()
    {
        $ilDB = $this->db;

        if (!isset($this->data_record)) {
            $query = "SELECT * FROM skl_tree_node WHERE obj_id = " .
                $ilDB->quote($this->id, "integer");
            $obj_set = $ilDB->query($query);
            $this->data_record = $ilDB->fetchAssoc($obj_set);
        }
        $this->setType($this->data_record["type"]);
        $this->setTitle($this->data_record["title"]);
        $this->setOrderNr($this->data_record["order_nr"]);
        $this->setSelfEvaluation($this->data_record["self_eval"]);
        $this->setStatus($this->data_record["status"]);
        $this->setImportId($this->data_record["import_id"]);
        $this->setCreationDate($this->data_record["creation_date"]);
    }

    /**
    * this method should only be called by class ilSCORM2004NodeFactory
    */
    public function setDataRecord($a_record)
    {
        $this->data_record = $a_record;
    }

    /**
     * Lookup Title
     *
     * @param	int			Node ID
     * @return	string		Title
     */
    protected static function _lookup($a_obj_id, $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT $a_field FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec[$a_field];
    }

    /**
     * Lookup Title
     *
     * @param	int			node ID
     * @return	string		title
     */
    public static function _lookupTitle($a_obj_id, $a_tref_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();

        include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
        if ($a_tref_id > 0 && ilSkillTemplateReference::_lookupTemplateId($a_tref_id) == $a_obj_id) {
            return self::_lookup($a_tref_id, "title");
        }
        return self::_lookup($a_obj_id, "title");
    }
    
    /**
     * Lookup self evaluation
     *
     * @param	int			node ID
     * @return	boolean		selectable? (self evaluation=
     */
    public static function _lookupSelfEvaluation($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        return self::_lookup($a_obj_id, "self_eval");
    }
    
    /**
     * Lookup Status
     *
     * @param int $a_obj_id node ID
     * @return int status
     */
    public static function _lookupStatus($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        return self::_lookup($a_obj_id, "status");
    }
    
    /**
    * Lookup Type
    *
    * @param	int			Node ID
    * @return	string		Type
    */
    public static function _lookupType($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["type"];
    }

    /**
     * Set status
     *
     * @param boolean $a_val status
     */
    public function setStatus($a_val)
    {
        $this->status = $a_val;
    }
    
    /**
     * Get status
     *
     * @return int status
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Write Title
     *
     * @param	int			Node ID
     * @param	string		Title
     */
    public static function _writeTitle($a_obj_id, $a_title)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE skl_tree_node SET " .
            " title = " . $ilDB->quote($a_title, "text") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($query);
    }

    /**
     * Write Order Nr
     *
     * @param	int			Node ID
     * @param	string		Order Nr
     */
    public static function _writeOrderNr($a_obj_id, $a_nr)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE skl_tree_node SET " .
            " order_nr = " . $ilDB->quote($a_nr, "integer") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
        $ilDB->manipulate($query);
    }
    
    /**
    * Create Node
    *
    * @param	boolean		Upload Mode
    */
    public function create()
    {
        $ilDB = $this->db;

        // insert object data
        $id = $ilDB->nextId("skl_tree_node");
        $query = "INSERT INTO skl_tree_node (obj_id, title, type, create_date, self_eval, order_nr, status, creation_date, import_id) " .
            "VALUES (" .
            $ilDB->quote($id, "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getType(), "text") . ", " .
            $ilDB->now() . ", " .
            $ilDB->quote((int) $this->getSelfEvaluation(), "integer") . ", " .
            $ilDB->quote((int) $this->getOrderNr(), "integer") . ", " .
            $ilDB->quote((int) $this->getStatus(), "integer") . ", " .
            $ilDB->now() . ", " .
            $ilDB->quote($this->getImportId(), "text") .
            ")";
        $ilDB->manipulate($query);
        $this->setId($id);
    }

    /**
    * Update Node
    */
    public function update()
    {
        $ilDB = $this->db;

        $query = "UPDATE skl_tree_node SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") .
            " ,self_eval = " . $ilDB->quote((int) $this->getSelfEvaluation(), "integer") .
            " ,order_nr = " . $ilDB->quote((int) $this->getOrderNr(), "integer") .
            " ,status = " . $ilDB->quote((int) $this->getStatus(), "integer") .
            " ,import_id = " . $ilDB->quote($this->getImportId(), "text") .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");

        $ilDB->manipulate($query);
    }

    /**
    * Delete Node
    */
    public function delete()
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM skl_tree_node WHERE obj_id= " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);
    }

    /**
     * Put this object into the skill tree
     */
    public static function putInTree($a_obj, $a_parent_id = "", $a_target_node_id = "")
    {
        $skill_tree = new ilSkillTree();

        // determine parent
        $parent_id = ($a_parent_id != "")
            ? $a_parent_id
            : $skill_tree->getRootId();

        // make a check, whether the type of object is allowed under
        // the parent
        $allowed = array(
            "skrt" => array("skll", "scat", "sktr", "sktp", "sctp"),
            "scat" => array("skll", "scat", "sktr"),
            "sctp" => array("sktp", "sctp"));
        $par_type = self::_lookupType($parent_id);
        if (!is_array($allowed[$par_type]) ||
            !in_array($a_obj->getType(), $allowed[$par_type])) {
            return;
        }
        
        // determine target
        if ($a_target_node_id != "") {
            $target = $a_target_node_id;
        } else {
            // determine last child that serves as predecessor
            $childs = $skill_tree->getChilds($parent_id);

            if (count($childs) == 0) {
                $target = IL_FIRST_NODE;
            } else {
                $target = $childs[count($childs) - 1]["obj_id"];
            }
        }

        if ($skill_tree->isInTree($parent_id) && !$skill_tree->isInTree($a_obj->getId())) {
            $skill_tree->insertNode($a_obj->getId(), $parent_id, $target);
        }
    }
    
    /**
    * Get scorm module editing tree
    *
    * @param	int		scorm module object id
    *
    * @return	object		tree object
    */
    public static function getTree($a_slm_obj_id)
    {
        $tree = new ilSkillTree();
        
        return $tree;
    }

    /**
     * Check for unique types
     */
    public static function uniqueTypesCheck($a_items)
    {
        $types = array();
        if (is_array($a_items)) {
            foreach ($a_items as $item) {
                $type = ilSkillTreeNode::_lookupType($item);
                $types[$type] = $type;
            }
        }

        if (count($types) > 1) {
            return false;
        }
        return true;
    }

    /**
     * Cut and copy a set of skills/skill categories into the clipboard
     */
    public static function clipboardCut($a_tree_id, $a_ids)
    {
        self::clearClipboard();
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $tree = new ilSkillTree();

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

        ilSkillTreeNode::clipboardCopy($a_tree_id, $cut_ids);

        // remove the objects from the tree
        // note: we are getting skills/categories which are *not* in the tree
        // we do not delete any pages/chapters here
        foreach ($cut_ids as $id) {
            $curnode = $tree->getNodeData($id);
            if ($tree->isInTree($id)) {
                $tree->deleteTree($curnode);
            }
        }
    }


    /**
     * Copy a set of skills/skill categories into the clipboard
     */
    public static function clipboardCopy($a_tree_id, $a_ids)
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        self::clearClipboard();
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $tree = new ilSkillTree();
        
        // put them into the clipboard
        $time = date("Y-m-d H:i:s", time());
        foreach ($a_ids as $id) {
            $curnode = "";
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
                ilSkillTreeNode::_lookupType($id),
                ilSkillTreeNode::_lookupTitle($id),
                0,
                $time,
                $order
            );
        }
    }


    /**
     * Insert basic skills from clipboard
     */
    public static function insertItemsFromClip($a_type, $a_obj_id)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        
        // @todo: move this to a service since it can be used here, too
        include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $tree = new ilSkillTree();
        
        $parent_id = $a_obj_id;
        $target = IL_LAST_NODE;

        // cut and paste
        $skills = $ilUser->getClipboardObjects($a_type);  // this will get all skills _regardless_ of level
        $copied_nodes = array();
        foreach ($skills as $skill) {
            // if skill was already copied as part of tree - do not copy it again
            if (!in_array($skill["id"], array_keys($copied_nodes))) {
                $cid = ilSkillTreeNode::pasteTree(
                    $skill["id"],
                    $parent_id,
                    $target,
                    $skill["insert_time"],
                    $copied_nodes,
                    (ilEditClipboard::getAction() == "copy"),
                    true
                );
                //				$target = $cid;
            }
        }

        //		if (ilEditClipboard::getAction() == "cut")
        //		{
        self::clearClipboard();
        //		}

        ilSkillTreeNode::saveChildsOrder(
            $a_obj_id,
            array(),
            in_array($a_type, array("sktp", "sctp"))
        );

        return $copied_nodes;
    }

    /**
     * Remove all skill items from clipboard
     *
     * @param
     * @return
     */
    public static function clearClipboard()
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        $ilUser->clipboardDeleteObjectsOfType("skll");
        $ilUser->clipboardDeleteObjectsOfType("scat");
        $ilUser->clipboardDeleteObjectsOfType("sktr");
        $ilUser->clipboardDeleteObjectsOfType("sktp");
        $ilUser->clipboardDeleteObjectsOfType("sctp");
        include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
        ilEditClipboard::clear();
    }
    
    
    /**
     * Paste item (tree) from clipboard to skill tree
     */
    public static function pasteTree(
        $a_item_id,
        $a_parent_id,
        $a_target,
        $a_insert_time,
        &$a_copied_nodes,
        $a_as_copy = false,
        $a_add_suffix = false
    ) {
        global $DIC;

        $ilUser = $DIC->user();
        $ilLog = $DIC["ilLog"];
        $lng = $DIC->language();

        $item_type = ilSkillTreeNode::_lookupType($a_item_id);

        if ($item_type == "scat") {
            include_once("./Services/Skill/classes/class.ilSkillCategory.php");
            $item = new ilSkillCategory($a_item_id);
        } elseif ($item_type == "skll") {
            include_once("./Services/Skill/classes/class.ilBasicSkill.php");
            $item = new ilBasicSkill($a_item_id);
        } elseif ($item_type == "sktr") {
            include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
            $item = new ilSkillTemplateReference($a_item_id);
        } elseif ($item_type == "sktp") {
            include_once("./Services/Skill/classes/class.ilBasicSkillTemplate.php");
            $item = new ilBasicSkillTemplate($a_item_id);
        } elseif ($item_type == "sctp") {
            include_once("./Services/Skill/classes/class.ilSkillTemplateCategory.php");
            $item = new ilSkillTemplateCategory($a_item_id);
        }

        $ilLog->write("Getting from clipboard type " . $item_type . ", " .
            "Item ID: " . $a_item_id);

        if ($a_as_copy) {
            $target_item = $item->copy();
            if ($a_add_suffix) {
                $target_item->setTitle($target_item->getTitle() . " " . $lng->txt("copy_of_suffix"));
                $target_item->update();
            }
            $a_copied_nodes[$item->getId()] = $target_item->getId();
        } else {
            $target_item = $item;
        }
        
        $ilLog->write("Putting into skill tree type " . $target_item->getType() .
            "Item ID: " . $target_item->getId() . ", Parent: " . $a_parent_id . ", " .
            "Target: " . $a_target);
        
        ilSkillTreeNode::putInTree($target_item, $a_parent_id, $a_target);
        
        $childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);

        foreach ($childs as $child) {
            ilSkillTreeNode::pasteTree(
                $child["id"],
                $target_item->getId(),
                IL_LAST_NODE,
                $a_insert_time,
                $a_copied_nodes,
                $a_as_copy
            );
        }
        
        return $target_item->getId();
    }

    /**
     * Is id in tree?
     *
     * @param
     * @return
     */
    public static function isInTree($a_id)
    {
        $skill_tree = new ilSkillTree();
        if ($skill_tree->isInTree($a_id)) {
            return true;
        }
        return false;
    }

    /**
     * Get all self evaluation nodes
     *
     * @param
     * @return
     */
    public static function getAllSelfEvaluationNodes()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT obj_id, title FROM skl_tree_node WHERE " .
            " self_eval = " . $ilDB->quote(true, "integer") . " ORDER BY TITLE "
        );
        $nodes = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $nodes[$rec["obj_id"]] = $rec["title"];
        }
        return $nodes;
    }

    /**
     * Get top skill templates and template categories
     *
     * @param
     * @return
     */
    public static function getTopTemplates()
    {
        $tr = new ilSkillTree();
        $childs = $tr->getChildsByTypeFilter($tr->getRootId(), array("sktp", "sctp"));
        
        return $childs;
    }
    
    /**
     * Get selectable skills
     *
     * @param
     * @return
     */
    public static function getSelectableSkills()
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM skl_tree_node " .
            " WHERE self_eval = " . $ilDB->quote(1, "integer")
        );
        
        $sel_skills = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $sel_skills[] = $rec;
        }
        
        return $sel_skills;
    }

    /**
     * Save childs order
     *
     * @param
     * @return
     */
    public static function saveChildsOrder($a_par_id, $a_childs_order, $a_templates = false)
    {
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $skill_tree = new ilSkillTree();
        
        if ($a_par_id != $skill_tree->readRootId()) {
            $childs = $skill_tree->getChilds($a_par_id);
        } else {
            if ($a_templates) {
                $childs = $skill_tree->getChildsByTypeFilter(
                    $a_par_id,
                    array("skrt", "sktp", "sctp")
                );
            } else {
                $childs = $skill_tree->getChildsByTypeFilter(
                    $a_par_id,
                    array("skrt", "skll", "scat", "sktr")
                );
            }
        }
        
        foreach ($childs as $k => $c) {
            if (isset($a_childs_order[$c["child"]])) {
                $childs[$k]["order_nr"] = (int) $a_childs_order[$c["child"]];
            }
        }
        
        $childs = ilUtil::sortArray($childs, "order_nr", "asc", true);

        $cnt = 10;
        foreach ($childs as $c) {
            ilSkillTreeNode::_writeOrderNr($c["child"], $cnt);
            $cnt += 10;
        }
    }

    /**
     * Get icon path
     *
     * @param int $a_obj_id node id
     * @param string $a_type node type
     * @param string $a_size size
     * @param int $a_status status
     * @return string icon path
     */
    public static function getIconPath($a_obj_id, $a_type, $a_size = "", $a_status = 0)
    {
        if ($a_status == self::STATUS_DRAFT && $a_type == "sctp") {
            $a_type = "scat";
        }
        if ($a_status == self::STATUS_DRAFT && $a_type == "sktp") {
            $a_type = "skll";
        }
        
        $off = ($a_status == self::STATUS_DRAFT)
            ? "_off"
            : "";
            
        $a_name = "icon_" . $a_type . $a_size . $off . ".svg";
        if ($a_type == "sktr") {
            include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
            $tid = ilSkillTemplateReference::_lookupTemplateId($a_obj_id);
            $type = ilSkillTreeNode::_lookupType($tid);
            if ($type == "sctp") {
                $a_name = "icon_sctr" . $a_size . $off . ".svg";
            }
        }
        $vers = "vers=" . str_replace(array(".", " "), "-", ILIAS_VERSION);
        return ilUtil::getImagePath($a_name) . "?" . $vers;
    }

    /**
     * Find skills
     *
     * @param
     * @return
     */
    public static function findSkills($a_term)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();
        $candidates = array();

        $skill_tree = new ilSkillTree();

        $sql = "SELECT * " .
            " FROM skl_tree_node" .
            " WHERE " . $ilDB->like("title", "text", "%" . $a_term . "%");
        $sql .= " ORDER BY title";
        $set = $ilDB->query($sql);
        include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
        while ($row = $ilDB->fetchAssoc($set)) {
            if (in_array($row["type"], array("sctp", "sktp"))) {
                // need to get "top template" first! (if it is already the top level, do not use it!)
                $path = $skill_tree->getSkillTreePath($row["obj_id"]);
                if ($path[1]["child"] != $row["obj_id"]) {
                    $trefs = ilSkillTemplateReference::_lookupTrefIdsForTopTemplateId($path[1]["child"]);
                    foreach ($trefs as $tref) {
                        $candidates[] = array("tref_id" => $tref, "skill_id" => $row["obj_id"], "title" => $row["title"]);
                    }
                }
            } elseif ($row["type"] == "sktr") {
                // works
                $candidates[] = array("tref_id" => $row["obj_id"], "skill_id" => ilSkillTemplateReference::_lookupTemplateId($row["obj_id"]), "title" => $row["title"]);
            } else {
                // works
                $candidates[] = array("tref_id" => 0, "skill_id" => $row["obj_id"], "title" => $row["title"]);
            }
        }

        foreach ($candidates as $c) {
            // if we get a path array, and the array has items try to use the data
            $path = $skill_tree->getSkillTreePath($c["skill_id"], $c["tref_id"]);
            $use = false;
            if (is_array($path) && count($path) > 0) {
                $use = true;
            }

            // if any inactive/outdated -> do not use the data
            if (is_array($path)) {
                foreach ($path as $p) {
                    if ($p["status"] > 0) {
                        $use = false;
                    }
                }
            }
            if ($use) {
                if (!in_array($c["title"], $res)) {
                    $res[] = $c["title"];
                }
            }
        }


        return $res;
    }

    /**
     * Get all possible common skill IDs for node IDs
     *
     * @param array $a_node_ids array of node ids
     * @return array array of skill ids
     */
    public static function getAllCSkillIdsForNodeIds(array $a_node_ids)
    {
        include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
        $cskill_ids = array();
        foreach ($a_node_ids as $id) {
            if (in_array(self::_lookupType($id), array("skll", "scat", "sktr"))) {
                $skill_id = $id;
                $tref_id = 0;
                if (ilSkillTreeNode::_lookupType($id) == "sktr") {
                    $skill_id = ilSkillTemplateReference::_lookupTemplateId($id);
                    $tref_id = $id;
                }
                $cskill_ids[] = array("skill_id" => $skill_id, "tref_id" => $tref_id);
            }
            if (in_array(ilSkillTreeNode::_lookupType($id), array("sktp", "sctp"))) {
                foreach (ilSkillTemplateReference::_lookupTrefIdsForTemplateId($id) as $tref_id) {
                    $cskill_ids[] = array("skill_id" => $id, "tref_id" => $tref_id);
                }
            }
            // for cats, skills and template references, get "real" usages
            // for skill and category templates check usage in references
        }
        return $cskill_ids;
    }
}
