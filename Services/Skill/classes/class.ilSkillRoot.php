<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Skill root node
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillRoot extends ilSkillTreeNode
{
    public $id;

    /**
     * Constructor
     * @access	public
     */
    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("skrt");
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
}
