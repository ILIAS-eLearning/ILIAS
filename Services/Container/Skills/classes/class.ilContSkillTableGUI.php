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
     * @var ilContainerGlobalProfiles
     */
    protected $container_global_profiles;

    /**
     * @var ilContainerLocalProfiles
     */
    protected $container_local_profiles;

    /**
     * @var ilContSkillCollector
     */
    protected $container_skill_collector;

    /**
     * Constructor
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        ilContainerSkills $a_cont_skills,
        ilContainerGlobalProfiles $a_cont_glb_profiles,
        ilContainerLocalProfiles $a_cont_lcl_profiles
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->skill_tree = new ilSkillTree();

        $this->container_skills = $a_cont_skills;
        $this->container_global_profiles = $a_cont_glb_profiles;
        $this->container_local_profiles = $a_cont_lcl_profiles;

        $this->container_skill_collector = new ilContSkillCollector(
            $this->container_skills,
            $this->container_global_profiles,
            $this->container_local_profiles
        );
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getSkills());
        $this->setTitle($this->lng->txt("cont_cont_skills"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("cont_skill"), "", "1");
        $this->addColumn($this->lng->txt("cont_path"), "", "1");
        $this->addColumn($this->lng->txt("cont_skill_profile"), "", "1");
        
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
        $skills = $this->container_skill_collector->getSkillsForTableGUI();

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

        $path = $this->getParentObject()->getPathString($a_set["base_skill_id"], $a_set["tref_id"]);
        $tpl->setVariable("PATH", $path);

        if ($a_set["profile"] != null) {
            $tpl->setVariable("PROFILE", $a_set["profile"]);
        } else {
            $tpl->setCurrentBlock("checkbox");
            $tpl->setVariable("ID", $a_set["base_skill_id"] . ":" . $a_set["tref_id"]);
            $tpl->parseCurrentBlock();
        }
    }
}
