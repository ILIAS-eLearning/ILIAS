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
 ********************************************************************
 */

/**
 * A node in the skill tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTreeNode
{
    protected ilDBInterface $db;
    protected ilSkillTree $skill_tree;
    protected string $type;
    protected int $id;
    protected string $title;
    protected string $description = "";
    protected bool $self_eval = false;
    protected int $order_nr;
    protected string $import_id = "";
    protected string $creation_date;
    protected int $status = 0;
    protected array $data_record;

    public const STATUS_PUBLISH = 0;
    public const STATUS_DRAFT = 1;
    public const STATUS_OUTDATED = 2;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $a_id;
        
        $this->skill_tree = new ilSkillTree();

        if ($a_id != 0) {
            $this->read();
        }
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getSkillTree() : ilSkillTree
    {
        return $this->skill_tree;
    }

    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setType(string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setId(int $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setSelfEvaluation(bool $a_val) : void
    {
        $this->self_eval = $a_val;
    }

    public function getSelfEvaluation() : bool
    {
        return $this->self_eval;
    }

    public function setOrderNr(int $a_val) : void
    {
        $this->order_nr = $a_val;
    }

    public function getOrderNr() : int
    {
        return $this->order_nr;
    }

    public function setImportId(string $a_val) : void
    {
        $this->import_id = $a_val;
    }

    public function getImportId() : string
    {
        return $this->import_id;
    }

    protected function setCreationDate(string $a_val) : void
    {
        $this->creation_date = $a_val;
    }

    public function getCreationDate() : string
    {
        return $this->creation_date;
    }

    /**
     * Get all status as array, key is value, value is lang text
     */
    public static function getAllStatus() : array
    {
        global $DIC;

        $lng = $DIC->language();

        return array(
            self::STATUS_DRAFT => $lng->txt("skmg_status_draft"),
            self::STATUS_PUBLISH => $lng->txt("skmg_status_publish"),
            self::STATUS_OUTDATED => $lng->txt("skmg_status_outdated")
        );
    }

    public static function getStatusInfo(int $a_status) : string
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
    public function read() : void
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
        $this->setDescription($this->data_record["description"] ?? "");
        $this->setOrderNr($this->data_record["order_nr"]);
        $this->setSelfEvaluation((bool) $this->data_record["self_eval"]);
        $this->setStatus($this->data_record["status"]);
        $this->setImportId($this->data_record["import_id"] ?? "");
        $this->setCreationDate($this->data_record["creation_date"] ?? "");
    }

    /**
    * this method should only be called by class ilSCORM2004NodeFactory
    */
    public function setDataRecord(array $a_record) : void
    {
        $this->data_record = $a_record;
    }

    protected static function _lookup(int $a_obj_id, string $a_field)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT $a_field FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec[$a_field];
    }

    public static function _lookupTitle(int $a_obj_id, int $a_tref_id = 0) : string
    {
        if ($a_tref_id > 0 && ilSkillTemplateReference::_lookupTemplateId($a_tref_id) == $a_obj_id) {
            return self::_lookup($a_tref_id, "title");
        }
        return self::_lookup($a_obj_id, "title");
    }

    public static function _lookupDescription(int $a_obj_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        return self::_lookup($a_obj_id, "description");
    }

    public static function _lookupSelfEvaluation(int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        return (bool) self::_lookup($a_obj_id, "self_eval");
    }

    public static function _lookupStatus(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        return (int) self::_lookup($a_obj_id, "status");
    }

    public static function _lookupType(int $a_obj_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["type"];
    }

    public function setStatus(int $a_val) : void
    {
        $this->status = $a_val;
    }

    public function getStatus() : int
    {
        return $this->status;
    }

    public static function _writeTitle(int $a_obj_id, string $a_title) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE skl_tree_node SET " .
            " title = " . $ilDB->quote($a_title, "text") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($query);
    }

    public static function _writeDescription(int $a_obj_id, string $a_description) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE skl_tree_node SET " .
            " description = " . $ilDB->quote($a_description, "clob") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($query);
    }

    public static function _writeOrderNr(int $a_obj_id, int $a_nr) : void
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
    */
    public function create() : void
    {
        $ilDB = $this->db;

        // insert object data
        $id = $ilDB->nextId("skl_tree_node");
        $query = "INSERT INTO skl_tree_node (obj_id, title, description, type, create_date, self_eval, order_nr, status, creation_date, import_id) " .
            "VALUES (" .
            $ilDB->quote($id, "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getDescription(), "clob") . "," .
            $ilDB->quote($this->getType(), "text") . ", " .
            $ilDB->now() . ", " .
            $ilDB->quote((int) $this->getSelfEvaluation(), "integer") . ", " .
            $ilDB->quote($this->getOrderNr(), "integer") . ", " .
            $ilDB->quote($this->getStatus(), "integer") . ", " .
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
            " ,description = " . $ilDB->quote($this->getDescription(), "clob") .
            " ,self_eval = " . $ilDB->quote((int) $this->getSelfEvaluation(), "integer") .
            " ,order_nr = " . $ilDB->quote($this->getOrderNr(), "integer") .
            " ,status = " . $ilDB->quote($this->getStatus(), "integer") .
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
    public static function putInTree(ilSkillTreeNode $a_obj, int $a_parent_id = 0, int $a_target_node_id = 0) : void
    {
        $skill_tree = new ilSkillTree();

        // determine parent
        $parent_id = ($a_parent_id != 0)
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
        if ($a_target_node_id != 0) {
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
    */
    public static function getTree(int $a_slm_obj_id) : ilSkillTree //seems not to be used
    {
        $tree = new ilSkillTree();
        
        return $tree;
    }

    /**
     * Check for unique types
     */
    public static function uniqueTypesCheck(array $a_items) : bool
    {
        $types = [];
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
     *
     * @throws ilInvalidTreeStructureException
     */
    public static function clipboardCut(int $a_tree_id, array $a_ids) : void
    {
        self::clearClipboard();
        $tree = new ilSkillTree();

        // get all "top" ids, i.e. remove ids, that have a selected parent
        $cut_ids = [];
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
    public static function clipboardCopy(int $a_tree_id, array $a_ids) : void
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        self::clearClipboard();
        $tree = new ilSkillTree();
        
        // put them into the clipboard
        $time = date("Y-m-d H:i:s", time());
        $order = 0;
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
    public static function insertItemsFromClip(string $a_type, int $a_obj_id) : array
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        // @todo: move this to a service since it can be used here, too

        $parent_id = $a_obj_id;
        $target = IL_LAST_NODE;

        // cut and paste
        $skills = $ilUser->getClipboardObjects($a_type);  // this will get all skills _regardless_ of level
        $copied_nodes = [];
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
            [],
            in_array($a_type, array("sktp", "sctp"))
        );

        return $copied_nodes;
    }

    /**
     * Remove all skill items from clipboard
     */
    public static function clearClipboard() : void
    {
        global $DIC;

        $ilUser = $DIC->user();
        
        $ilUser->clipboardDeleteObjectsOfType("skll");
        $ilUser->clipboardDeleteObjectsOfType("scat");
        $ilUser->clipboardDeleteObjectsOfType("sktr");
        $ilUser->clipboardDeleteObjectsOfType("sktp");
        $ilUser->clipboardDeleteObjectsOfType("sctp");
        ilEditClipboard::clear();
    }
    
    
    /**
     * Paste item (tree) from clipboard to skill tree
     */
    public static function pasteTree(
        int $a_item_id,
        int $a_parent_id,
        int $a_target,
        string $a_insert_time,
        array &$a_copied_nodes,
        bool $a_as_copy = false,
        bool $a_add_suffix = false
    ) : int {
        global $DIC;

        $ilUser = $DIC->user();
        $ilLog = $DIC["ilLog"];
        $lng = $DIC->language();

        $item_type = ilSkillTreeNode::_lookupType($a_item_id);

        $item = null;
        if ($item_type == "scat") {
            $item = new ilSkillCategory($a_item_id);
        } elseif ($item_type == "skll") {
            $item = new ilBasicSkill($a_item_id);
        } elseif ($item_type == "sktr") {
            $item = new ilSkillTemplateReference($a_item_id);
        } elseif ($item_type == "sktp") {
            $item = new ilBasicSkillTemplate($a_item_id);
        } elseif ($item_type == "sctp") {
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
     */
    public static function isInTree(int $a_id) : bool
    {
        $skill_tree = new ilSkillTree();
        if ($skill_tree->isInTree($a_id)) {
            return true;
        }
        return false;
    }

    public static function getAllSelfEvaluationNodes() : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT obj_id, title FROM skl_tree_node WHERE " .
            " self_eval = " . $ilDB->quote(true, "integer") . " ORDER BY TITLE "
        );
        $nodes = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $nodes[$rec["obj_id"]] = $rec["title"];
        }
        return $nodes;
    }

    /**
     * Get top skill templates and template categories
     */
    public static function getTopTemplates() : array
    {
        $tr = new ilSkillTree();
        $childs = $tr->getChildsByTypeFilter($tr->getRootId(), array("sktp", "sctp"));
        
        return $childs;
    }

    public static function getSelectableSkills() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM skl_tree_node " .
            " WHERE self_eval = " . $ilDB->quote(1, "integer")
        );
        
        $sel_skills = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $sel_skills[] = $rec;
        }
        
        return $sel_skills;
    }

    public static function saveChildsOrder(int $a_par_id, array $a_childs_order, bool $a_templates = false) : void
    {
        $skill_tree = new ilSkillTree();
        
        if ($a_par_id != $skill_tree->readRootId()) {
            $childs = $skill_tree->getChilds($a_par_id);
        } elseif ($a_templates) {
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

    public static function getIconPath(int $a_obj_id, string $a_type, string $a_size = "", int $a_status = 0) : string
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
            $tid = ilSkillTemplateReference::_lookupTemplateId($a_obj_id);
            $type = ilSkillTreeNode::_lookupType($tid);
            if ($type == "sctp") {
                $a_name = "icon_sctr" . $a_size . $off . ".svg";
            }
        }
        $vers = "vers=" . str_replace(array(".", " "), "-", ILIAS_VERSION);
        return ilUtil::getImagePath($a_name) . "?" . $vers;
    }

    public static function findSkills(string $a_term) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = [];
        $candidates = [];

        $skill_tree = new ilSkillTree();

        $sql = "SELECT * " .
            " FROM skl_tree_node" .
            " WHERE " . $ilDB->like("title", "text", "%" . $a_term . "%");
        $sql .= " ORDER BY title";
        $set = $ilDB->query($sql);
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
     */
    public static function getAllCSkillIdsForNodeIds(array $a_node_ids) : array
    {
        $cskill_ids = [];
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
