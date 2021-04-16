<?php

class ilBasicSkillTreeDBRepository implements ilBasicSkillTreeRepository
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db)
            ?: $DIC->database();
    }

    /**
     * @inheritDoc
     */
    public function getCommonSkillIdForImportId(
        ilSkillTree $tree,
        int $a_source_inst_id,
        int $a_skill_import_id,
        int $a_tref_import_id = 0
    ) : array {
        $ilDB = $this->db;

        $template_ids = array();
        if ($a_tref_import_id > 0) {
            $skill_node_type = "sktp";

            // get all matching tref nodes
            $set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) " .
                " WHERE n.import_id = " . $ilDB->quote("il_" . ($a_source_inst_id) . "_sktr_" . $a_tref_import_id,
                    "text") .
                " ORDER BY n.creation_date DESC ");
            while ($rec = $ilDB->fetchAssoc($set)) {
                if (($t = ilSkillTemplateReference::_lookupTemplateId($rec["obj_id"])) > 0) {
                    $template_ids[$t] = $rec["obj_id"];
                }
            }
        } else {
            $skill_node_type = "skll";
        }
        $set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) " .
            " WHERE n.import_id = " . $ilDB->quote("il_" . ($a_source_inst_id) . "_" . $skill_node_type . "_" . $a_skill_import_id,
                "text") .
            " ORDER BY n.creation_date DESC ");
        $results = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $matching_trefs = array();
            if ($a_tref_import_id > 0) {
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
                $results[] = array("skill_id" => $rec["obj_id"],
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
            " WHERE l.import_id = " . $ilDB->quote("il_" . ($a_source_inst_id) . "_sklv_" . $a_level_import_id,
                "text") .
            " ORDER BY l.creation_date DESC ");
        $results = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $results[] = array("level_id" => $rec["id"], "creation_date" => $rec["creation_date"]);
        }
        return $results;
    }
}