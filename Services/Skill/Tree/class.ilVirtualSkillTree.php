<?php

declare(strict_types=1);

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
 * Virtual skill tree
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesSkill
 */
class ilVirtualSkillTree
{
    protected ilLanguage $lng;
    protected ilSkillTree $tree;

    /**
     * @var ?array<int, array{parent: int, lft: int, order_nr: int}>
     */
    protected static ?array $order_node_data = null;
    protected bool $include_drafts = false;

    /**
     * @var string[]
     */
    protected array $drafts = [];
    protected bool $include_outdated = false;

    /**
     * @var string[]
     */
    protected array $outdated = [];

    public function __construct(int $tree_id)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->skills()->internal()->factory()->tree()->getTreeById($tree_id);
    }

    /**
     * @return array{id: string, cskill_id: string}
     */
    public function getRootNode(): array
    {
        $root_id = $this->tree->readRootId();
        $root_node = $this->tree->getNodeData($root_id);
        unset($root_node["child"]);
        $root_node["id"] = $root_id . ":0";
        $root_node["cskill_id"] = $root_id . ":0";

        return $root_node;
    }

    public function setIncludeDrafts(bool $a_val): void
    {
        $this->include_drafts = $a_val;
    }

    public function getIncludeDrafts(): bool
    {
        return $this->include_drafts;
    }

    public function setIncludeOutdated(bool $a_val): void
    {
        $this->include_outdated = $a_val;
    }

    public function getIncludeOutdated(): bool
    {
        return $this->include_outdated;
    }

    public function getNode(string $a_vtree_id): array
    {
        $id_parts = explode(":", $a_vtree_id);
        $skl_tree_id = (int) $id_parts[0];
        $skl_template_tree_id = isset($id_parts[1]) ? (int) $id_parts[1] : 0;

        if ($skl_template_tree_id == 0
            || (ilSkillTemplateReference::_lookupTemplateId($skl_tree_id) == $skl_template_tree_id)) {
            $node_data = $this->tree->getNodeData($skl_tree_id);
            if (isset($node_data["parent"])) {
                $node_data["parent"] = $node_data["parent"] . ":0";
            }
        } else {
            $node_data = $this->tree->getNodeData($skl_template_tree_id);
            $node_data["parent"] = $skl_tree_id . ":" . $node_data["parent"];
        }

        unset($node_data["child"]);
        unset($node_data["skl_tree_id"]);
        unset($node_data["lft"]);
        unset($node_data["rgt"]);
        unset($node_data["depth"]);

        $node_data["id"] = $a_vtree_id;
        $cid = $this->getCSkillIdForVTreeId($a_vtree_id);
        $cid_parts = explode(":", $cid);
        $node_data["skill_id"] = $cid_parts[0];
        $node_data["tref_id"] = $cid_parts[1];
        $node_data["cskill_id"] = $cid;

        return $node_data;
    }

    /**
     * @return array{cskill_id: string, id: string, skill_id: string, tref_id: string, parent: string, type: string}[]
     */
    public function getChildsOfNode(string $a_parent_id): array
    {
        $a_parent_id_parts = explode(":", $a_parent_id);
        $a_parent_skl_tree_id = (int) $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = isset($a_parent_id_parts[1]) ? (int) $a_parent_id_parts[1] : 0;

        if ($a_parent_skl_template_tree_id == 0) {
            $childs = $this->tree->getChildsByTypeFilter($a_parent_skl_tree_id, array("scat", "skll", "sktr"), "order_nr");
        } else {
            $childs = $this->tree->getChildsByTypeFilter($a_parent_skl_template_tree_id, array("sktp", "sctp"), "order_nr");
        }

        $drafts = [];
        $outdated = [];
        foreach ($childs as $k => $c) {
            if ($a_parent_skl_template_tree_id > 0) {
                // we are in template tree only
                $child_id = $a_parent_skl_tree_id . ":" . $c["child"];
            } elseif (!in_array($c["type"], array("sktr", "sctr"))) {
                // we are in main tree only
                $child_id = $c["child"] . ":0";
            } else {
                // get template id for references
                $child_id = $c["child"] . ":" . ilSkillTemplateReference::_lookupTemplateId((int) $c["child"]);
            }
            unset($childs[$k]["child"]);
            unset($childs[$k]["skl_tree_id"]);
            unset($childs[$k]["lft"]);
            unset($childs[$k]["rgt"]);
            unset($childs[$k]["depth"]);
            $childs[$k]["id"] = $child_id;
            $cid = $this->getCSkillIdForVTreeId($child_id);
            $cid_parts = explode(":", $cid);
            $childs[$k]["skill_id"] = $cid_parts[0];
            $childs[$k]["tref_id"] = $cid_parts[1];
            $childs[$k]["cskill_id"] = $cid;
            $childs[$k]["parent"] = $a_parent_id;

            // @todo: prepare this for tref id?
            if (ilSkillTreeNode::_lookupStatus((int) $c["child"]) == ilSkillTreeNode::STATUS_DRAFT ||
                in_array($a_parent_id, $this->drafts)) {
                $this->drafts[] = $child_id;
                $drafts[] = $k;
            }
            if (ilSkillTreeNode::_lookupStatus((int) $c["child"]) == ilSkillTreeNode::STATUS_OUTDATED ||
                in_array($a_parent_id, $this->outdated)) {
                $this->outdated[] = $child_id;
                $outdated[] = $k;
            }
        }
        if (!$this->getIncludeDrafts()) {
            foreach ($drafts as $d) {
                unset($childs[$d]);
            }
        }
        if (!$this->getIncludeOutdated()) {
            foreach ($outdated as $d) {
                unset($childs[$d]);
            }
        }

        return $childs;
    }

    public function getChildsOfNodeForCSkillId(string $a_cskill_id): array
    {
        $id_parts = explode(":", $a_cskill_id);
        if (!isset($id_parts[1]) || $id_parts[1] == 0) {
            $id = $id_parts[0] . ":0";
        } else {
            $id = $id_parts[1] . ":" . $id_parts[0];
        }
        return $this->getChildsOfNode($id);
    }

    public function getCSkillIdForVTreeId(string $a_vtree_id): string
    {
        $id_parts = explode(":", $a_vtree_id);
        if (!isset($id_parts[1]) || $id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $id_parts[0];
            $tref_id = 0;
        } else {
            // skill in template
            $tref_id = $id_parts[0];
            $skill_id = $id_parts[1];
        }
        return $skill_id . ":" . $tref_id;
    }

    public function getVTreeIdForCSkillId(string $a_cskill_id): string
    {
        $id_parts = explode(":", $a_cskill_id);
        if (!isset($id_parts[1]) || $id_parts[1] == 0) {
            $id = $id_parts[0] . ":0";
        } else {
            $id = $id_parts[1] . ":" . $id_parts[0];
        }
        return $id;
    }

    public function getNodeTitle(array $a_node): string
    {
        $lng = $this->lng;

        $a_parent_id_parts = explode(":", $a_node["id"]);
        $a_parent_skl_tree_id = (int) $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = isset($a_parent_id_parts[1]) ? (int) $a_parent_id_parts[1] : 0;

        // title
        $title = $a_node["title"];

        // root?
        if ($a_node["type"] == "skrt") {
            $lng->txt("skmg_skills");
        } elseif ($a_node["type"] == "sktr") {
            //				$title.= " (".ilSkillTreeNode::_lookupTitle($a_parent_skl_template_tree_id).")";
        }

        return $title;
    }

    /**
     * @return array{cskill_id: string, id: string, skill_id: string, tref_id: string, parent: string, type: string}[]
     */
    public function getSubTreeForCSkillId(string $a_cskill_id, bool $a_only_basic = false): array
    {
        $id_parts = explode(":", $a_cskill_id);
        if (!isset($id_parts[1]) || $id_parts[1] == 0) {
            $id = $id_parts[0] . ":0";
        } else {
            $id = $id_parts[1] . ":" . $id_parts[0];
        }

        $result = [];

        $node = $this->getNode($id);
        if (!$a_only_basic || in_array($node["type"], array("skll", "sktp")) ||
            ($node["type"] == "sktr" && ilSkillTreeNode::_lookupType((int) $node["skill_id"]) == "sktp")) {
            $result[] = $node;
        }
        return array_merge($result, $this->__getSubTreeRec($id, $a_only_basic));
    }

    /**
     * @return array{cskill_id: string, id: string, skill_id: string, tref_id: string, parent: string, type: string}[]
     */
    protected function __getSubTreeRec(string $id, bool $a_only_basic): array
    {
        $result = [];
        $childs = $this->getChildsOfNode($id);
        foreach ($childs as $c) {
            if (!$a_only_basic || in_array($c["type"], array("skll", "sktp")) ||
                ($c["type"] == "sktr" && ilSkillTreeNode::_lookupType($c["skill_id"]) == "sktp")) {
                $result[] = $c;
            }
            $result = array_merge($result, $this->__getSubTreeRec($c["id"], $a_only_basic));
        }

        return $result;
    }

    public function isDraft(string $a_node_id): bool
    {
        return in_array($a_node_id, $this->drafts);
    }

    public function isOutdated(string $a_node_id): bool
    {
        return in_array($a_node_id, $this->outdated);
    }

    /**
     * Get ordererd nodeset for common skill ids
     *
     * @param string[]|array[] $c_skill_ids string of "skill_id:tref_id" skill ids or an array
     * @param string $a_skill_id_key if first parameter is array[], this string identifies the key of the basic skill id
     * @param string $a_tref_id_key if first parameter is array[], this string identifies the key of the tref id
     * @return string[]|array[]
     */
    public function getOrderedNodeset(array $c_skill_ids, string $a_skill_id_key = "", string $a_tref_id_key = ""): array
    {
        global $DIC;

        $db = $DIC->database();

        if (self::$order_node_data == null) {
            $node_data = [];
            $set = $db->query("SELECT t.child, t.parent, t.lft, n.order_nr FROM skl_tree t JOIN skl_tree_node n ON (t.child = n.obj_id)");
            while ($rec = $db->fetchAssoc($set)) {
                $node_data[(int) $rec["child"]] = array(
                    "parent" => null === $rec["parent"] ? null : (int) $rec["parent"],
                    "lft" => (int) $rec["lft"],
                    "order_nr" => (int) $rec["order_nr"],
                );
            }
            self::$order_node_data = $node_data;
        } else {
            $node_data = self::$order_node_data;
        }

        uasort($c_skill_ids, function ($a, $b) use ($node_data, $a_skill_id_key, $a_tref_id_key): int {
            // normalize to cskill strings
            if (is_array($a)) {
                $cskilla = $a[$a_skill_id_key] . ":" . $a[$a_tref_id_key];
                $cskillb = $b[$a_skill_id_key] . ":" . $b[$a_tref_id_key];
            } else {
                $cskilla = $a;
                $cskillb = $b;
            }

            // get vtree ids
            $vida = explode(":", $this->getVTreeIdForCSkillId($cskilla));
            $vidb = explode(":", $this->getVTreeIdForCSkillId($cskillb));

            $ua = $this->getFirstUncommonAncestors($vida[0], $vidb[0], $node_data);
            if (is_array($ua)) {
                return ($node_data[$ua[0]]["order_nr"] - $node_data[$ua[1]]["order_nr"]);
            }
            // if we did not find a first uncommon ancestor, we are in the same node in the
            // main tree, here, if we have tref ids, we let the template tree decide
            if ($vida[1] > 0 && $vidb[1] > 0) {
                $ua = $this->getFirstUncommonAncestors($vida[1], $vidb[1], $node_data);
                if (is_array($ua)) {
                    return ($node_data[$ua[0]]["order_nr"] - $node_data[$ua[1]]["order_nr"]);
                }
            }

            return 0;
        });

        return $c_skill_ids;
    }

    /**
     * get path in node data
     */
    protected function getPath(string $a, array $node_data): array
    {
        $path[] = $a;
        while ($node_data[$a]["parent"] != 0) {
            $a = $node_data[$a]["parent"];
            $path[] = $a;
        }
        return array_reverse($path);
    }

    /**
     * get first uncommon ancestors of $a and $b in $node_data
     *
     * @return array{0: mixed, 1: mixed}|false
     */
    protected function getFirstUncommonAncestors(string $a, string $b, array $node_data)
    {
        $path_a = $this->getPath($a, $node_data);
        $path_b = $this->getPath($b, $node_data);
        foreach ($path_a as $k => $v) {
            if ($v != $path_b[$k]) {
                return array($v, $path_b[$k]);
            }
        }
        return false;
    }
}
