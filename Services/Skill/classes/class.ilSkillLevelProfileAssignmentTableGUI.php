<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for skill profile skill level assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillLevelProfileAssignmentTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var int
     */
    protected $skill_id;

    /**
     * @var int
     */
    protected $tref_id;

    /**
     * @var ilBasicSkill
     */
    protected $skill;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_level_id;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_cskill_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->request = $DIC->http()->request();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $parts = explode(":", $a_cskill_id);
        $this->skill_id = (int) $parts[0];
        $this->tref_id = (int) $parts[1];

        $params = $this->request->getQueryParams();
        $this->requested_level_id = (int) ($params["level_id"] ?? 0);

        $this->skill = new ilBasicSkill($this->skill_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->skill->getLevelData());
        $this->setTitle($this->skill->getTitle() . ", " .
            $lng->txt("skmg_skill_levels"));
        
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_level_profile_assignment_row.html", "Services/Skill");
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("CMD", $lng->txt("skmg_assign_level"));
        $ilCtrl->setParameter($this->parent_obj, "level_id", (int) $a_set["id"]);
        $this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget(
            $this->parent_obj,
            "assignLevelToProfile"
        ));
        $ilCtrl->setParameter($this->parent_obj, "level_id", $this->requested_level_id);
        $this->tpl->parseCurrentBlock();
        
        $this->tpl->setVariable("TITLE", $a_set["title"]);
    }
}
