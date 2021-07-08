<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * A node in the skill tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
    public $description;

    /**
    * @param	int		node id
    */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $a_id;
        
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
     * Set description
     *
     * @param	string		$a_description	description
     */
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }

    /**
     * Get description
     *
     * @return	string		description
     */
    public function getDescription()
    {
        return $this->description;
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
        $this->setDescription($this->data_record["description"]);
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
        if ($a_tref_id > 0 && ilSkillTemplateReference::_lookupTemplateId($a_tref_id) == $a_obj_id) {
            return self::_lookup($a_tref_id, "title");
        }
        return self::_lookup($a_obj_id, "title");
    }

    /**
     * Lookup Description
     *
     * @param	int			node ID
     * @return	string		description
     */
    public static function _lookupDescription($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        return self::_lookup($a_obj_id, "description");
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
     * Write Description
     *
     * @param	int			Node ID
     * @param	string		Description
     */
    public static function _writeDescription($a_obj_id, $a_description)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE skl_tree_node SET " .
            " description = " . $ilDB->quote($a_description, "clob") .
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
        $query = "INSERT INTO skl_tree_node (obj_id, title, description, type, create_date, self_eval, order_nr, status, creation_date, import_id) " .
            "VALUES (" .
            $ilDB->quote($id, "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getDescription(), "clob") . "," .
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
            " ,description = " . $ilDB->quote($this->getDescription(), "clob") .
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
     * Get all possible common skill IDs for node IDs
     *
     * @param array $a_node_ids array of node ids
     * @return array array of skill ids
     */
    public static function getAllCSkillIdsForNodeIds(array $a_node_ids)
    {
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
