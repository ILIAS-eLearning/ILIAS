<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for competences in containers
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesContainer
 */
class ilContSkillTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilContainerSkills
     */
    protected $container_skills;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilContainerSkills $a_cont_skills)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $this->skill_tree = new ilSkillTree();

        $this->container_skills = $a_cont_skills;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getSkills());
        $this->setTitle($this->lng->txt("cont_cont_skills"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("cont_skill"), "", "1");
        $this->addColumn($this->lng->txt("cont_path"), "", "1");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.cont_skill_row.html", "Services/Container/Skills");
        $this->setSelectAllCheckbox("id");

        $this->addMultiCommand("confirmRemoveSelectedSkill", $this->lng->txt("remove"));
        //$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Get skills
     *
     * @param
     * @return
     */
    public function getSkills()
    {
        $skills = array();
        foreach ($this->container_skills->getSkills() as $sk) {
            $skills[] = array(
                "skill_id" => $sk["skill_id"],
                "tref_id" => $sk["tref_id"],
                "title" => ilBasicSkill::_lookupTitle($sk["skill_id"], $sk["tref_id"])
            );
        }

        // order skills per virtual skill tree
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $skills = $vtree->getOrderedNodeset($skills, "skill_id", "tref_id");

        return $skills;
    }


    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $skill_tree = $this->skill_tree;

        $tpl->setVariable("TITLE", $a_set["title"]);
        $tpl->setVariable("ID", $a_set["skill_id"] . ":" . $a_set["tref_id"]);

        $path = $this->getParentObject()->getPathString($a_set["skill_id"], $a_set["tref_id"]);

        $tpl->setVariable("PATH", $path);
    }
}
