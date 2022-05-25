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

use ILIAS\Skill\Tree\SkillTreeFactory;
use ILIAS\Skill\Tree\SkillTreeNodeManager;
use ILIAS\Skill\Service\SkillInternalManagerService;
use ILIAS\Skill\Service\SkillInternalFactoryService;

/**
 * Skill Data set class
 *
 * This class implements the following entities:
 * - skmg: Skill management top entity (no data, only dependecies)
 * - skee: Skill tree
 * - skl_subtree: Skill subtree (includes data of skl_tree, skl_tree_node and skl_templ_ref)
 * - skl_templ_subtree: Skill template subtree (includes data of skl_tree and skl_tree_node)
 * - skl_level: Skill levels
 * - skl_prof: skill profiles (different mode)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillDataSet extends ilDataSet
{
    public const MODE_SKILLS = "";
    public const MODE_PROFILES = "prof";

    protected int $skill_tree_id = 0;
    protected int $skill_tree_root_id = 0;
    protected int $init_top_order_nr = 0;
    protected int $init_templ_top_order_nr = 0;

    /**
     * @var int[]
     */
    protected array $selected_nodes = [];

    /**
     * @var int[]
     */
    protected array $selected_profiles = [];
    protected string $mode = "";

    protected SkillInternalManagerService $skill_manager;
    protected SkillTreeFactory $skill_tree_factory;
    protected SkillInternalFactoryService $skill_factory;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct();

        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->skill_factory = $DIC->skills()->internal()->factory();
    }

    public function setMode(string $a_val) : void
    {
        $this->mode = $a_val;
    }

    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * @param int[] $a_val
     */
    public function setSelectedNodes(array $a_val) : void
    {
        $this->selected_nodes = $a_val;
    }

    /**
     * @return int[]
     */
    public function getSelectedNodes() : array
    {
        return $this->selected_nodes;
    }

    /**
     * @param int[] $a_val (profile ids)
     */
    public function setSelectedProfiles(array $a_val) : void
    {
        $this->selected_profiles = $a_val;
    }

    /**
     * @return int[] (profile ids)
     */
    public function getSelectedProfiles() : array
    {
        return $this->selected_profiles;
    }

    public function setSkillTreeId(int $skill_tree_id) : void
    {
        $this->skill_tree_id = $skill_tree_id;
    }

    public function getSkillTreeId() : int
    {
        return $this->skill_tree_id;
    }

    /**
     * @return string[]
     */
    public function getSupportedVersions() : array
    {
        return array("5.1.0", "7.0", "8.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Services/Skill/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "skmg") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                    return array(
                        "Mode" => "text"
                    );
                case "8.0":
                    return array(
                        "Id" => "integer"
                    );
            }
        }
        if ($a_entity == "skee") {
            switch ($a_version) {
                case "8.0":
                    return array(
                        "Id" => "integer",
                        "Mode" => "text"
                    );
            }
        }
        if ($a_entity == "skl_subtree") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                case "8.0":
                    return array(
                            "SklTreeId" => "integer",
                            "TopNode" => "integer",
                            "Child" => "integer",
                            "Parent" => "integer",
                            "Depth" => "integer",
                            "Type" => "text",
                            "Title" => "text",
                            "Description" => "text",
                            "SelfEval" => "integer",
                            "OrderNr" => "integer",
                            "Status" => "integer",
                            "TemplateId" => "integer"
                    );
            }
        }
        if ($a_entity == "skl_templ_subtree") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                case "8.0":
                    return array(
                            "SklTreeId" => "integer",
                            "TopNode" => "integer",
                            "Child" => "integer",
                            "Parent" => "integer",
                            "Depth" => "integer",
                            "Type" => "text",
                            "Title" => "text",
                            "Description" => "text",
                            "SelfEval" => "integer",
                            "OrderNr" => "integer",
                            "Status" => "integer"
                    );
            }
        }
        if ($a_entity == "skl_level") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                case "8.0":
                    return array(
                            "LevelId" => "integer",
                            "SkillId" => "integer",
                            "Nr" => "integer",
                            "Title" => "text",
                            "Description" => "text"
                    );
            }
        }
        if ($a_entity == "skl_prof") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text"
                    );
                case "8.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "SkillTreeId" => "integer"
                    );
            }
        }
        if ($a_entity == "skl_local_prof") {
            switch ($a_version) {
                case "7.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "RefId" => "integer"
                    );
                case "8.0":
                    return array(
                            "Id" => "integer",
                            "Title" => "text",
                            "Description" => "text",
                            "RefId" => "integer",
                            "SkillTreeId" => "integer"
                    );
            }
        }
        if ($a_entity == "skl_prof_level") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                            "ProfileId" => "integer",
                            "BaseSkillId" => "integer",
                            "TrefId" => "integer",
                            "LevelId" => "integer"
                    );
                case "7.0":
                case "8.0":
                    return array(
                        "ProfileId" => "integer",
                        "BaseSkillId" => "integer",
                        "TrefId" => "integer",
                        "LevelId" => "integer",
                        "OrderNr" => "integer"
                    );
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;
        $skill_tree = $this->skill_tree_factory->getTreeById($this->getSkillTreeId());

        $this->data = [];

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        if ($a_entity == "skmg") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                    if ($this->getMode() == self::MODE_SKILLS) {
                        $this->data[] = array("Mode" => "Skills");
                    } elseif ($this->getMode() == self::MODE_PROFILES) {
                        $this->data[] = array("Mode" => "Profiles");
                    }
                    break;
                case "8.0":
                    $this->data[] = [
                        "Id" => $this->getSkillTreeId()
                    ];
                    break;
            }
        }
        if ($a_entity == "skee") {	// dummy node
            switch ($a_version) {
                case "8.0":
                foreach ($a_ids as $id) {
                    if ($this->getMode() == self::MODE_SKILLS) {
                        $this->data[] = array(
                            "Id" => $id,
                            "Mode" => "Skills"
                        );
                    } elseif ($this->getMode() == self::MODE_PROFILES) {
                        $this->data[] = array(
                            "Id" => $id,
                            "Mode" => "Profiles"
                        );
                    }
                }
                break;

            }
        }
        if ($a_entity == "skl_subtree") {	// get subtree for top node
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                case "8.0":
                    foreach ($a_ids as $id) {
                        $sub = $skill_tree->getSubTree($skill_tree->getNodeData($id));
                        foreach ($sub as $s) {
                            $set = $ilDB->query(
                                "SELECT * FROM skl_templ_ref " .
                                " WHERE skl_node_id = " . $ilDB->quote($s["child"], "integer")
                            );
                            $rec = $ilDB->fetchAssoc($set);

                            $top_node = ($s["child"] == $id)
                                    ? 1
                                    : 0;
                            $this->data[] = array(
                                    "SklTreeId" => $s["skl_tree_id"],
                                    "TopNode" => $top_node,
                                    "Child" => $s["child"],
                                    "Parent" => $s["parent"],
                                    "Depth" => $s["depth"],
                                    "Type" => $s["type"],
                                    "Title" => $s["title"],
                                    "Description" => $s["description"],
                                    "SelfEval" => $s["self_eval"],
                                    "OrderNr" => $s["order_nr"],
                                    "Status" => $s["status"],
                                    "TemplateId" => (int) $rec["templ_id"]
                                );
                        }
                    }
                    break;

            }
        }

        if ($a_entity == "skl_templ_subtree") {	// get template subtree for template id
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                case "8.0":
                    foreach ($a_ids as $id) {
                        $sub = $skill_tree->getSubTree($skill_tree->getNodeData($id));
                        foreach ($sub as $s) {
                            $top_node = ($s["child"] == $id)
                                    ? 1
                                    : 0;
                            $this->data[] = array(
                                    "SklTreeId" => $s["skl_tree_id"],
                                    "TopNode" => $top_node,
                                    "Child" => $s["child"],
                                    "Parent" => $s["parent"],
                                    "Depth" => $s["depth"],
                                    "Type" => $s["type"],
                                    "Title" => $s["title"],
                                    "Description" => $s["description"],
                                    "SelfEval" => $s["self_eval"],
                                    "OrderNr" => $s["order_nr"],
                                    "Status" => $s["status"]
                            );
                        }
                    }
                    break;

            }
        }

        if ($a_entity == "skl_level") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                case "8.0":
                    $this->getDirectDataFromQuery("SELECT id level_id, skill_id, nr, title, description" .
                            " FROM skl_level WHERE " .
                            $ilDB->in("skill_id", $a_ids, false, "integer") . " ORDER BY skill_id ASC, nr ASC");
                    break;

            }
        }

        if ($a_entity == "skl_prof") {
            switch ($a_version) {
                case "5.1.0":
                case "7.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description" .
                            " FROM skl_profile WHERE " .
                            $ilDB->in("id", $a_ids, false, "integer"));
                    break;
                case "8.0":
                    $set = $ilDB->query(
                        "SELECT id, title, description FROM skl_profile " .
                        " WHERE " . $ilDB->in("id", $a_ids, false, "integer")
                    );
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $this->data[] = [
                            "Id" => $rec["id"],
                            "Title" => $rec["title"],
                            "Description" => $rec["description"],
                            "SkillTreeId" => $this->getSkillTreeId()
                        ];
                    }
                    break;

            }
        }

        if ($a_entity == "skl_local_prof") {
            switch ($a_version) {
                case "7.0":
                    foreach ($a_ids as $obj_id) {
                        $obj_ref_id = ilObject::_getAllReferences($obj_id);
                        $obj_ref_id = end($obj_ref_id);
                        $profiles = $this->skill_manager->getProfileManager()->getLocalProfilesForObject($obj_ref_id);
                        $profile_ids = [];
                        foreach ($profiles as $p) {
                            $profile_ids[] = $p["id"];
                        }
                        $set = $ilDB->query(
                            "SELECT * FROM skl_profile " .
                            " WHERE " . $ilDB->in("id", $profile_ids, false, "integer")
                        );
                        while ($rec = $ilDB->fetchAssoc($set)) {
                            $this->data[] = [
                                "Id" => $rec["id"],
                                "Title" => $rec["title"],
                                "Description" => $rec["description"],
                                "RefId" => $obj_ref_id
                            ];
                        }
                    }
                    break;
                case "8.0":
                    foreach ($a_ids as $obj_id) {
                        $obj_ref_id = ilObject::_getAllReferences($obj_id);
                        $obj_ref_id = end($obj_ref_id);
                        $profiles = $this->skill_manager->getProfileManager()->getLocalProfilesForObject($obj_ref_id);
                        $profile_ids = [];
                        foreach ($profiles as $p) {
                            $profile_ids[] = $p["id"];
                        }
                        $set = $ilDB->query(
                            "SELECT * FROM skl_profile " .
                            " WHERE " . $ilDB->in("id", $profile_ids, false, "integer")
                        );
                        while ($rec = $ilDB->fetchAssoc($set)) {
                            $this->data[] = [
                                "Id" => $rec["id"],
                                "Title" => $rec["title"],
                                "Description" => $rec["description"],
                                "RefId" => $obj_ref_id,
                                "SkillTreeId" => $this->getSkillTreeId()
                            ];
                        }
                    }
                    break;

            }
        }

        if ($a_entity == "skl_prof_level") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT profile_id, base_skill_id, tref_id, level_id" .
                            " FROM skl_profile_level WHERE " .
                            $ilDB->in("profile_id", $a_ids, false, "integer"));
                    break;
                case "7.0":
                case "8.0":
                    $this->getDirectDataFromQuery("SELECT profile_id, base_skill_id, tref_id, level_id, order_nr" .
                        " FROM skl_profile_level WHERE " .
                        $ilDB->in("profile_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    /**
     * @param array{Id: int, Child: int, Type: string} $a_rec
     *
     * @return array<string, array{ids: int[]}>
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        $ilDB = $this->db;

        switch ($a_entity) {
            case "skmg":
                $deps["skee"]["ids"][] = $this->getSkillTreeId();
                return $deps;

            case "skee":
                if (is_null($a_rec["Id"])) {
                    return [];
                }
                $skill_tree = $this->skill_tree_factory->getTreeById($a_rec["Id"]);

                $deps = [];
                if ($this->getMode() == self::MODE_SKILLS) {
                    // determine top nodes of main tree to be exported and all referenced template nodes
                    $sel_nodes = $this->getSelectedNodes();
                    $exp_types = array("skll", "scat", "sctr", "sktr");
                    if (!is_array($sel_nodes)) {
                        $childs = $skill_tree->getChildsByTypeFilter($skill_tree->readRootId(), $exp_types);
                        $skl_subtree_deps = [];
                        foreach ($childs as $c) {
                            $skl_subtree_deps[] = $c["child"];
                        }
                    } else {
                        $skl_subtree_deps = [];
                        foreach ($sel_nodes as $n) {
                            if (in_array(ilSkillTreeNode::_lookupType($n), $exp_types)) {
                                $skl_subtree_deps[] = $n;
                            }
                        }
                    }

                    // determine template subtrees
                    $ref_nodes = [];
                    if (is_array($skl_subtree_deps)) {
                        foreach ($skl_subtree_deps as $id) {
                            if (ilSkillTreeNode::_lookupType($id) == "sktr") {
                                $ref_nodes[$id] = $id;
                            } else {
                                $sub = $skill_tree->getSubTree($skill_tree->getNodeData($id), true, ["sktr"]);
                                foreach ($sub as $s) {
                                    $ref_nodes[$s["child"]] = $s["child"];
                                }
                            }
                        }
                    }

                    $set = $ilDB->query("SELECT DISTINCT(templ_id) FROM skl_templ_ref " .
                            " WHERE " . $ilDB->in("skl_node_id", $ref_nodes, false, "integer"));
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $deps["skl_templ_subtree"]["ids"][] = (int) $rec["templ_id"];
                    }

                    // export subtree after templates
                    $deps["skl_subtree"]["ids"] = $skl_subtree_deps;
                } elseif ($this->getMode() == self::MODE_PROFILES) {
                    foreach ($this->getSelectedProfiles() as $p_id) {
                        $deps["skl_prof"]["ids"][] = $p_id;
                    }
                }

                return $deps;

            case "skl_subtree":
            case "skl_templ_subtree":
                $deps = [];
                if (in_array($a_rec["Type"], array("skll", "sktp"))) {
                    $deps["skl_level"]["ids"][] = $a_rec["Child"];
                }
                return $deps;

            case "skl_prof":
            case "skl_local_prof":
                $deps["skl_prof_level"]["ids"][] = $a_rec["Id"] ?? null;
                return $deps;
        }

        return [];
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        $skill_tree = $this->skill_tree_factory->getTreeById($this->getSkillTreeId());
        $skill_tree_root_id = $skill_tree->readRootId();
        $tree_node_manager = $this->skill_manager->getTreeNodeManager($this->getSkillTreeId());

        $init_top_order_nr = $skill_tree->getMaxOrderNr($skill_tree_root_id);
        $init_templ_top_order_nr = $skill_tree->getMaxOrderNr($skill_tree_root_id, true);

        $source_inst_id = $a_mapping->getInstallId();
        switch ($a_entity) {
            case "skl_subtree":
                if ($a_rec["TopNode"] == 1) {
                    $parent = $skill_tree_root_id;
                    $status = ilSkillTreeNode::STATUS_DRAFT;
                    $order = $a_rec["OrderNr"] + $init_top_order_nr;
                } else {
                    $parent = (int) $a_mapping->getMapping("Services/Skill", "skl_tree", $a_rec["Parent"]);
                    $status = $a_rec["Status"];
                    $order = $a_rec["OrderNr"];
                }
                switch ($a_rec["Type"]) {
                    case "scat":
                        $scat = new ilSkillCategory();
                        $scat->setTitle($a_rec["Title"]);
                        $scat->setDescription($a_rec["Description"] ?? "");
                        $scat->setImportId("il_" . $source_inst_id . "_scat_" . $a_rec["Child"]);
                        $scat->setSelfEvaluation((bool) $a_rec["SelfEval"]);
                        $scat->setOrderNr($order);
                        $scat->setStatus($status);
                        $scat->create();
                        $tree_node_manager->putIntoTree($scat, $parent);
                        $a_mapping->addMapping("Services/Skill", "skl_tree", $a_rec["Child"], $scat->getId());
                        break;

                    case "skll":
                        $skll = new ilBasicSkill();
                        $skll->setTitle($a_rec["Title"]);
                        $skll->setDescription($a_rec["Description"] ?? "");
                        $skll->setImportId("il_" . $source_inst_id . "_skll_" . $a_rec["Child"]);
                        $skll->setSelfEvaluation((bool) $a_rec["SelfEval"]);
                        $skll->setOrderNr($order);
                        $skll->setStatus($status);
                        $skll->create();
                        $tree_node_manager->putIntoTree($skll, $parent);
                        $a_mapping->addMapping("Services/Skill", "skl_tree", $a_rec["Child"], $skll->getId());
                        break;

                    case "sktr":
                        $template_id = (int) $a_mapping->getMapping("Services/Skill", "skl_tree", $a_rec["TemplateId"]);
                        // only create template references, if referenced template is found (template trees are imported first)
                        if ($template_id > 0) {
                            $sktr = new ilSkillTemplateReference();
                            $sktr->setTitle($a_rec["Title"]);
                            $sktr->setDescription($a_rec["Description"] ?? "");
                            $sktr->setImportId("il_" . $source_inst_id . "_sktr_" . $a_rec["Child"]);
                            $sktr->setSelfEvaluation((bool) $a_rec["SelfEval"]);
                            $sktr->setOrderNr($order);
                            $sktr->setSkillTemplateId($template_id);
                            $sktr->setStatus($status);
                            $sktr->create();
                            $tree_node_manager->putIntoTree($sktr, $parent);
                            $a_mapping->addMapping("Services/Skill", "skl_tree", $a_rec["Child"], $sktr->getId());
                        }
                        break;

                }
                break;

            case "skl_templ_subtree":
                if ($a_rec["TopNode"] == 1) {
                    $parent = $skill_tree_root_id;
                    $order = $a_rec["OrderNr"] + $init_templ_top_order_nr;
                } else {
                    $parent = (int) $a_mapping->getMapping("Services/Skill", "skl_tree", $a_rec["Parent"]);
                    $order = $a_rec["OrderNr"];
                }
                switch ($a_rec["Type"]) {
                    case "sctp":
                        $sctp = new ilSkillTemplateCategory();
                        $sctp->setTitle($a_rec["Title"]);
                        $sctp->setDescription($a_rec["Description"] ?? "");
                        $sctp->setImportId("il_" . $source_inst_id . "_sctp_" . $a_rec["Child"]);
                        $sctp->setOrderNr($order);
                        $sctp->create();
                        $tree_node_manager->putIntoTree($sctp, $parent);
                        $a_mapping->addMapping("Services/Skill", "skl_tree", $a_rec["Child"], $sctp->getId());
                        break;

                    case "sktp":
                        $sktp = new ilBasicSkillTemplate();
                        $sktp->setTitle($a_rec["Title"]);
                        $sktp->setDescription($a_rec["Description"] ?? "");
                        $sktp->setImportId("il_" . $source_inst_id . "_sktp_" . $a_rec["Child"]);
                        $sktp->setOrderNr($order);
                        $sktp->create();
                        $tree_node_manager->putIntoTree($sktp, $parent);
                        $a_mapping->addMapping("Services/Skill", "skl_tree", $a_rec["Child"], $sktp->getId());
                        break;
                }
                break;

            case "skl_level":
                $skill_id = (int) $a_mapping->getMapping("Services/Skill", "skl_tree", $a_rec["SkillId"]);
                $type = ilSkillTreeNode::_lookupType($skill_id);
                if (in_array($type, array("skll", "sktp"))) {
                    if ($type == "skll") {
                        $skill = new ilBasicSkill($skill_id);
                    } else {
                        $skill = new ilBasicSkillTemplate($skill_id);
                    }
                    $skill->addLevel($a_rec["Title"], $a_rec["Description"], "il_" . $source_inst_id . "_sklv_" . $a_rec["LevelId"]);
                    $skill->update();
                }
                break;

            case "skl_prof":
                $profile = $this->skill_factory->profile(
                    0,
                    $a_rec["Title"],
                    $a_rec["Description"] ?? "",
                    $this->getSkillTreeId()
                );
                $new_profile = $this->skill_manager->getProfileManager()->createProfile($profile);

                $a_mapping->addMapping("Services/Skill", "skl_prof", $a_rec["Id"], $new_profile->getId());
                break;

            case "skl_local_prof":
                $profile = $this->skill_factory->profile(
                    0,
                    $a_rec["Title"],
                    $a_rec["Description"] ?? "",
                    $this->getSkillTreeId(),
                    "",
                    $a_rec["RefId"]
                );
                $new_profile = $this->skill_manager->getProfileManager()->createProfile($profile);

                $a_mapping->addMapping("Services/Skill", "skl_local_prof", $a_rec["Id"], $new_profile->getId());
                break;

            case "skl_prof_level":
                $profile_id = (int) $a_mapping->getMapping("Services/Skill", "skl_prof", $a_rec["ProfileId"])
                    ? (int) $a_mapping->getMapping("Services/Skill", "skl_prof", $a_rec["ProfileId"])
                    : (int) $a_mapping->getMapping("Services/Skill", "skl_local_prof", $a_rec["ProfileId"]);
                if ($profile_id > 0) {
                    $prof = $this->skill_manager->getProfileManager()->getById($profile_id);
                    $level_id_data = ilBasicSkill::getLevelIdForImportId($this->getCurrentInstallationId(), $a_rec["LevelId"]);
                    $skill_data = ilBasicSkill::getCommonSkillIdForImportId($this->getCurrentInstallationId(), $a_rec["BaseSkillId"], $a_rec["TrefId"]);
                    $level_id = $tref_id = $base_skill = 0;
                    foreach ($level_id_data as $l) {
                        reset($skill_data);
                        foreach ($skill_data as $s) {
                            //		echo "<br>=".ilBasicSkill::lookupLevelSkillId($l["level_id"])."=".$s["skill_id"]."=";

                            if ($level_id == 0 && ilBasicSkill::lookupLevelSkillId($l["level_id"]) == $s["skill_id"]) {
                                $level_id = $l["level_id"];
                                $base_skill = $s["skill_id"];
                                $tref_id = $s["tref_id"];
                            }
                        }
                    }
                    if ($level_id > 0) {
                        $prof->addSkillLevel($base_skill, $tref_id, $level_id, $a_rec["OrderNr"]);
                    }
                    $this->skill_manager->getProfileManager()->updateProfile($prof);
                }
                break;
        }
    }
}
