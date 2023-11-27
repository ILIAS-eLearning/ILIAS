<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill Template Category
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTemplateCategory extends ilSkillTreeNode
{
    public $id;

    /**
     * Constructor
     * @access	public
     */
    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sctp");
    }

    /**
     * Copy skill category
     */
    public function copy()
    {
        $sctp = new ilSkillTemplateCategory();
        $sctp->setTitle($this->getTitle());
        $sctp->setDescription($this->getDescription());
        $sctp->setType($this->getType());
        $sctp->setOrderNr($this->getOrderNr());
        $sctp->create();

        return $sctp;
    }

    public function delete()
    {
        $ilDB = $this->db;

        $sctp_id = $this->getId();
        $childs = $this->skill_tree->getChildsByTypeFilter(
            $sctp_id,
            ["sktp", "sctp"]
        );
        foreach ($childs as $node) {
            switch ($node["type"]) {
                case "sktp":
                    $obj = new ilBasicSkillTemplate((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "sctp":
                    $obj = new ilSkillTemplateCategory((int) $node["obj_id"]);
                    $obj->delete();
                    break;
            }
        }

        foreach (\ilSkillTemplateReference::_lookupTrefIdsForTopTemplateId($sctp_id) as $tref_id) {
            $obj = ilSkillTreeNodeFactory::getInstance($tref_id);
            $node_data = $this->skill_tree->getNodeData($tref_id);
            if (is_object($obj)) {
                $obj->delete();
            }
            if ($this->skill_tree->isInTree($tref_id)) {
                $this->skill_tree->deleteTree($node_data);
            }
        }

        $ilDB->manipulate(
            "DELETE FROM skl_templ_ref WHERE "
            . " templ_id = " . $ilDB->quote($this->getId(), "integer")
        );

        parent::delete();
    }
}
