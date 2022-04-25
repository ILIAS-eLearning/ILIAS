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

use ILIAS\Skill\Tree;

class ilBasicSkillTreeDBRepository implements ilBasicSkillTreeRepository
{
    protected ilDBInterface $db;
    protected Tree\SkillTreeFactory $tree_factory;

    public function __construct(Tree\SkillTreeFactory $tree_factory, ilDBInterface $db = null)
    {
        global $DIC;

        $this->tree_factory = $tree_factory;
        $this->db = ($db)
            ?: $DIC->database();
    }

    /**
     * @inheritDoc
     */
    public function getCommonSkillIdForImportId(
        int $a_source_inst_id,
        int $a_skill_import_id,
        int $a_tref_import_id = 0
    ) : array {
        $ilDB = $this->db;

        $template_ids = [];
        if ($a_tref_import_id > 0) {
            $skill_node_type = "sktp";

            // get all matching tref nodes
            $set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) " .
                " WHERE n.import_id = " . $ilDB->quote(
                    "il_" . ($a_source_inst_id) . "_sktr_" . $a_tref_import_id,
                    "text"
                ) .
                " ORDER BY n.creation_date DESC ");
            while ($rec = $ilDB->fetchAssoc($set)) {
                if (($t = ilSkillTemplateReference::_lookupTemplateId($rec["obj_id"])) > 0) {
                    $template_ids[$t] = (int) $rec["obj_id"];
                }
            }
        } else {
            $skill_node_type = "skll";
        }
        $set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) " .
            " WHERE n.import_id = " . $ilDB->quote(
                "il_" . ($a_source_inst_id) . "_" . $skill_node_type . "_" . $a_skill_import_id,
                "text"
            ) .
            " ORDER BY n.creation_date DESC ");
        $results = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $matching_trefs = [];
            if ($a_tref_import_id > 0) {
                $tree = $this->tree_factory->getTreeById($rec["skl_tree_id"]);
                $skill_template_id = $tree->getTopParentNodeId($rec["obj_id"]);

                // check of skill is in template
                foreach ($template_ids as $templ => $tref) {
                    if ($skill_template_id == $templ) {
                        $matching_trefs[] = $tref;
                    }
                }
            } else {
                $matching_trefs = array(0);
            }

            foreach ($matching_trefs as $t) {
                $results[] = array("skill_id" => (int) $rec["obj_id"],
                                   "tref_id" => $t,
                                   "creation_date" => $rec["creation_date"]
                );
            }
        }
        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getLevelIdForImportId(int $a_source_inst_id, int $a_level_import_id) : array
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM skl_level l JOIN skl_tree t ON (l.skill_id = t.child) " .
            " WHERE l.import_id = " . $ilDB->quote(
                "il_" . ($a_source_inst_id) . "_sklv_" . $a_level_import_id,
                "text"
            ) .
            " ORDER BY l.creation_date DESC ");
        $results = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $results[] = array("level_id" => (int) $rec["id"], "creation_date" => $rec["creation_date"]);
        }
        return $results;
    }

    public function isInAnyTree(int $node_id) : bool
    {
        $tree_id = $this->getTreeIdForNodeId($node_id);
        if ($tree_id > 0) {
            return true;
        }
        return false;
    }

    public function getTreeIdForNodeId(int $node_id) : int
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM skl_tree " .
            " WHERE child = %s ",
            ["integer"],
            [$node_id]
        );
        $rec = $db->fetchAssoc($set);
        return $rec["skl_tree_id"] ?? 0;
    }

    public function getTreeForNodeId(int $node_id) : ilSkillTree
    {
        $tree_id = $this->getTreeIdForNodeId($node_id);
        return $this->tree_factory->getTreeById($tree_id);
    }

    public function getVirtualTreeForNodeId(int $node_id) : ilVirtualSkillTree
    {
        $tree_id = $this->getTreeIdForNodeId($node_id);
        return $this->tree_factory->getVirtualTreeById($tree_id);
    }
}
