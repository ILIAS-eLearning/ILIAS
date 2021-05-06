<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Self evaluation table for single skill
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillSelfEvalSkillTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var int
     */
    protected $sn_id;

    /**
     * @var ilSkillSelfEvaluation
     */
    protected $se;

    /**
     * @var array
     */
    protected $levels;

    /**
     * @var ilBasicSkill
     */
    protected $skill;

    
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_sn_id, $a_se = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        $this->sn_id = $a_sn_id;
        if ($a_se != null) {
            $this->se = $a_se;
            $this->levels = $this->se->getLevels();
        }

        // build title
        $stree = new ilSkillTree();
        $path = $stree->getPathFull($this->sn_id);
        $title = $sep = "";
        foreach ($path as $p) {
            if ($p["type"] != "skrt") {
                $title .= $sep . $p["title"];
                $sep = " > ";
            }
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getLevels());
        $this->setTitle($title);
        $this->setLimit(9999);
        
        $this->addColumn($this->lng->txt("skmg_your_self_evaluation"));
        $this->addColumn($this->lng->txt("skmg_skill_level"));
        
        $this->setEnableHeader(true);
        //		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.self_eval_row.html", "Services/Skill");
        $this->disable("footer");
        $this->setEnableTitle(true);
        
        //		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Get levels
     *
     * @param
     * @return
     */
    public function getLevels()
    {
        $this->skill = new ilBasicSkill($this->sn_id);
        $levels = array(array("id" => 0));
        foreach ($this->skill->getLevelData() as $k => $v) {
            $levels[] = $v;
        }

        return $levels;
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        //var_dump($a_set);
        if ($a_set["id"] == 0) {
            $this->tpl->setVariable("LEVEL_ID", $a_set["id"]);
            $this->tpl->setVariable("SKILL_ID", $this->sn_id);
            $this->tpl->setVariable("TXT_SKILL", $lng->txt("skmg_no_skills"));
        } else {
            $this->tpl->setVariable("LEVEL_ID", $a_set["id"]);
            $this->tpl->setVariable("SKILL_ID", $this->sn_id);
            $this->tpl->setVariable("TXT_SKILL", $a_set["title"] . ": " . $a_set["description"]);
        }

        if ($this->se != null) {
            if ($this->levels[$this->sn_id] == $a_set["id"]) {
                $this->tpl->setVariable("CHECKED", " checked='checked' ");
            }
        } elseif ($a_set["id"] == 0) {
            $this->tpl->setVariable("CHECKED", " checked='checked' ");
        }
    }
}
