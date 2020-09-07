<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Skill Category
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
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
        parent::delete();
    }

    /**
     * Copy skill category
     */
    public function copy()
    {
        $scat = new ilSkillCategory();
        $scat->setTitle($this->getTitle());
        $scat->setType($this->getType());
        $scat->setSelfEvaluation($this->getSelfEvaluation());
        $scat->setOrderNr($this->getOrderNr());
        $scat->create();

        return $scat;
    }
}
