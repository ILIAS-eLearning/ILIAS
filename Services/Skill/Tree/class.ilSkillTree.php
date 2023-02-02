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
 * Skill tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTree extends ilTree
{
    protected array $by_type_data = [];

    public function __construct(int $a_tree_id = 1)
    {
        parent::__construct($a_tree_id);
        $this->setTreeTablePK("skl_tree_id");
        $this->setTableNames('skl_tree', 'skl_tree_node');
    }

    /**
     * @return array{skill_id: int, child: int, tref_id: int, parent: int}[]
     */
    public function getSkillTreePath(int $a_base_skill_id, int $a_tref_id = 0): array
    {
        if ($a_tref_id > 0) {
            $path = $this->getPathFull($a_tref_id);
            $sub_path = $this->getPathFull($a_base_skill_id);
            if (is_array($path)) {
                foreach ($path as $k => $v) {
                    if ($v["child"] != $a_tref_id) {
                        $path[$k]["skill_id"] = $v["child"];
                        $path[$k]["tref_id"] = 0;
                    } else {
                        $path[$k]["skill_id"] = ilSkillTemplateReference::_lookupTemplateId($a_tref_id);
                        $path[$k]["tref_id"] = $a_tref_id;
                    }
                }
            }
            $found = false;
            if (is_array($sub_path)) {
                foreach ($sub_path as $s) {
                    if ($found) {
                        $s["skill_id"] = $s["child"];
                        $s["tref_id"] = $a_tref_id;
                        $path[] = $s;
                    }
                    if ($s["child"] == ilSkillTemplateReference::_lookupTemplateId($a_tref_id)) {
                        $found = true;
                    }
                }
            }
        } else {
            $path = $this->getPathFull($a_base_skill_id);
            if (is_array($path)) {
                foreach ($path as $k => $v) {
                    $path[$k]["skill_id"] = $v["child"];
                    $path[$k]["tref_id"] = 0;
                }
            }
        }

        if (is_array($path)) {
            return $path;
        }
        return [];
    }

    public function getSkillTreePathAsString(int $a_base_skill_id, int $a_tref_id = 0): string
    {
        $path = $this->getSkillTreePath($a_base_skill_id, $a_tref_id);
        $str = "";
        $sep = "";
        foreach ($path as $p) {
            if ($p["type"] != "skrt" && $p["child"] != $a_base_skill_id) {
                $str .= $sep . $p["title"];
                $sep = " > ";
            }
        }
        return $str;
    }

    public function getTopParentNodeId(int $a_node_id): int
    {
        $path = $this->getPathId($a_node_id);
        return $path[1];
    }

    public function getMaxOrderNr(int $a_par_id, bool $a_templates = false): int
    {
        if ($a_par_id != $this->readRootId()) {
            $childs = $this->getChilds($a_par_id);
        } elseif ($a_templates) {
            $childs = $this->getChildsByTypeFilter(
                $a_par_id,
                array("skrt", "sktp", "sctp")
            );
        } else {
            $childs = $this->getChildsByTypeFilter(
                $a_par_id,
                array("skrt", "skll", "scat", "sktr")
            );
        }

        $max = 0;
        foreach ($childs as $k => $c) {
            $max = max(array((int) $c["order_nr"], $max));
        }

        return $max;
    }

    public function initChildsData()
    {
        if (isset($this->by_type_data[$this->getTreeId()])) {
            return;
        }

        $db = $this->db;
        $set = $db->queryF(
            "SELECT * FROM " .
            "skl_tree JOIN skl_tree_node ON skl_tree.child=skl_tree_node.obj_id " .
            " WHERE skl_tree.skl_tree_id = %s ",
            ["integer"],
            [$this->getTreeId()]
        );
        $this->by_type_data[$this->getTreeId()] = [];
        while ($rec = $db->fetchAssoc($set)) {
            $this->by_type_data[$this->getTreeId()][$rec["parent"]][$rec["type"]][] = $rec;
        }
    }

    public function getChildsByTypeFilter($a_node_id, $a_types, $a_order = "", $a_direction = "ASC"): array
    {
        $this->initChildsData();
        $childs = [];
        foreach ($a_types as $type) {
            $type_childs = $this->by_type_data[$this->getTreeId()][$a_node_id][$type] ?? [];
            $childs = array_merge($childs, $type_childs);
        }

        if ($a_order != "") {
            ilArrayUtil::sortArray($childs, $a_order, $a_direction);
        }

        return $childs;
    }
}
