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
    protected string $type = "";
    protected int $id = 0;
    protected string $title = "";
    protected string $description = "";
    protected bool $self_eval = false;
    protected int $order_nr = 0;
    protected string $import_id = "";
    protected string $creation_date = "";
    protected int $status = 0;

    /**
     * @var array{
     *   type: string,
     *   title: string,
     *   description: string,
     *   order_nr: int,
     *   self_eval: bool,
     *   status: int,
     *   import_id: string,
     *   creation_date: string
     * }
     */
    protected array $data_record = [];

    public const STATUS_PUBLISH = 0;
    public const STATUS_DRAFT = 1;
    public const STATUS_OUTDATED = 2;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $a_id;

        if ($a_id != 0) {
            $this->read();
        }
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSkillTree(): ilSkillTree
    {
        return $this->skill_tree;
    }

    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setType(string $a_type): void
    {
        $this->type = $a_type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setSelfEvaluation(bool $a_val): void
    {
        $this->self_eval = $a_val;
    }

    public function getSelfEvaluation(): bool
    {
        return $this->self_eval;
    }

    public function setOrderNr(int $a_val): void
    {
        $this->order_nr = $a_val;
    }

    public function getOrderNr(): int
    {
        return $this->order_nr;
    }

    public function setImportId(string $a_val): void
    {
        $this->import_id = $a_val;
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }

    protected function setCreationDate(string $a_val): void
    {
        $this->creation_date = $a_val;
    }

    public function getCreationDate(): string
    {
        return $this->creation_date;
    }

    /**
     * Get all status as array, key is value, value is lang text
     */
    public static function getAllStatus(): array
    {
        global $DIC;

        $lng = $DIC->language();

        return array(
            self::STATUS_DRAFT => $lng->txt("skmg_status_draft"),
            self::STATUS_PUBLISH => $lng->txt("skmg_status_publish"),
            self::STATUS_OUTDATED => $lng->txt("skmg_status_outdated")
        );
    }

    public static function getStatusInfo(int $a_status): string
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
    public function read(): void
    {
        $ilDB = $this->db;

        if (empty($this->data_record)) {
            $query = "SELECT * FROM skl_tree_node WHERE obj_id = " .
                $ilDB->quote($this->id, "integer");
            $obj_set = $ilDB->query($query);
            $this->data_record = $ilDB->fetchAssoc($obj_set);
        }
        $this->data_record["order_nr"] = (int) $this->data_record["order_nr"];
        $this->data_record["self_eval"] = (bool) $this->data_record["self_eval"];
        $this->data_record["status"] = (int) $this->data_record["status"];
        $this->setType($this->data_record["type"]);
        $this->setTitle($this->data_record["title"]);
        $this->setDescription($this->data_record["description"] ?? "");
        $this->setOrderNr($this->data_record["order_nr"]);
        $this->setSelfEvaluation($this->data_record["self_eval"]);
        $this->setStatus($this->data_record["status"]);
        $this->setImportId($this->data_record["import_id"] ?? "");
        $this->setCreationDate($this->data_record["creation_date"] ?? "");
    }

    /**
    * this method should only be called by class ilSCORM2004NodeFactory
    */
    public function setDataRecord(array $a_record): void
    {
        $this->data_record = $a_record;
    }

    protected static function _lookup(int $a_obj_id, string $a_field): ?string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT $a_field FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return isset($obj_rec[$a_field]) ? (string) $obj_rec[$a_field] : null;
    }

    public static function _lookupTitle(int $a_obj_id, int $a_tref_id = 0): string
    {
        if ($a_tref_id > 0 && ilSkillTemplateReference::_lookupTemplateId($a_tref_id) == $a_obj_id) {
            return self::_lookup($a_tref_id, "title");
        }
        return (string) self::_lookup($a_obj_id, "title");
    }

    public static function _lookupDescription(int $a_obj_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        return (string) self::_lookup($a_obj_id, "description");
    }

    public static function _lookupSelfEvaluation(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        return (bool) self::_lookup($a_obj_id, "self_eval");
    }

    public static function _lookupStatus(int $a_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        return (int) self::_lookup($a_obj_id, "status");
    }

    public static function _lookupType(int $a_obj_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM skl_tree_node WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        return $obj_rec["type"] ?? "";
    }

    public function setStatus(int $a_val): void
    {
        $this->status = $a_val;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public static function _writeTitle(int $a_obj_id, string $a_title): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE skl_tree_node SET " .
            " title = " . $ilDB->quote($a_title, "text") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($query);
    }

    public static function _writeDescription(int $a_obj_id, string $a_description): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE skl_tree_node SET " .
            " description = " . $ilDB->quote($a_description, "clob") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($query);
    }

    public static function _writeOrderNr(int $a_obj_id, int $a_nr): void
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
    public function create(): void
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

    public function delete(): void
    {
        $ilDB = $this->db;

        $query = "DELETE FROM skl_tree_node WHERE obj_id= " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);
    }

    /**
     * Check for unique types
     */
    public static function uniqueTypesCheck(array $a_items): bool
    {
        $types = [];
        foreach ($a_items as $item) {
            $type = ilSkillTreeNode::_lookupType($item);
            $types[$type] = $type;
        }

        if (count($types) > 1) {
            return false;
        }
        return true;
    }

    /**
     * @return array<int, string>
     */
    public static function getAllSelfEvaluationNodes(): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT obj_id, title FROM skl_tree_node WHERE " .
            " self_eval = " . $ilDB->quote(true, "integer") . " ORDER BY TITLE "
        );
        $nodes = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec["obj_id"] = (int) $rec["obj_id"];
            $nodes[$rec["obj_id"]] = $rec["title"];
        }
        return $nodes;
    }

    /**
     * @return array{obj_id: int, order_nr: int, status: int, self_eval: bool, title: string, type: string, create_date: string, description: string}[]
     */
    public static function getSelectableSkills(): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_tree_node " .
            " WHERE self_eval = " . $ilDB->quote(1, "integer")
        );

        $sel_skills = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['obj_id'] = (int) $rec['obj_id'];
            $rec['order_nr'] = (int) $rec['order_nr'];
            $rec['status'] = (int) $rec['status'];
            $rec['self_eval'] = (bool) $rec['self_eval'];
            $sel_skills[] = $rec;
        }

        return $sel_skills;
    }

    public static function getIconPath(int $a_obj_id, string $a_type, string $a_size = "", int $a_status = 0): string
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
     */
    public static function getAllCSkillIdsForNodeIds(array $a_node_ids): array
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
