<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill Category
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillCategory extends ilSkillTreeNode
{
    public $id;

    /**
     * Constructor
     * @access	public
     */
    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("scat");
    }

    /**
     * Read data from database
     */
    public function read()
    {
        parent::read();
    }

    /**
     * Create skill
     *
     */
    public function create()
    {
        parent::create();
    }

    /**
     * Delete skill
     */
    public function delete()
    {
        $scat_id = $this->getId();
        $childs = $this->skill_tree->getChildsByTypeFilter(
            $scat_id,
            ["skll", "scat", "sktr"]
        );
        foreach ($childs as $node) {
            switch ($node["type"]) {
                case "skll":
                    $obj = new ilBasicSkill((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "scat":
                    $obj = new ilSkillCategory((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "sktr":
                    $obj = new ilSkillTemplateReference((int) $node["obj_id"]);
                    $obj->delete();
                    break;
            }
        }

        parent::delete();
    }

    /**
     * Copy skill category
     */
    public function copy()
    {
        $scat = new ilSkillCategory();
        $scat->setTitle($this->getTitle());
        $scat->setDescription($this->getDescription());
        $scat->setType($this->getType());
        $scat->setSelfEvaluation($this->getSelfEvaluation());
        $scat->setOrderNr($this->getOrderNr());
        $scat->create();

        return $scat;
    }
}
