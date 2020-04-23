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
}
