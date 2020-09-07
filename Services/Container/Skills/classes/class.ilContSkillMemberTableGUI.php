<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for container members / skill assignments
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesContainer
 */
class ilContSkillMemberTableGUI extends ilTable2GUI
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
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilContainerSkills $a_cont_skills)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ui = $DIC->ui();

        $this->setId("cont_skll_mem_" . $a_cont_skills->getId());

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $this->skill_tree = new ilSkillTree();

        $this->container_skills = $a_cont_skills;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getMembers());
        $this->setTitle($this->lng->txt("cont_cont_skills"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("cont_mem_skills"), "");
        $this->addColumn($this->lng->txt("cont_published"), "");
        $this->addColumn($this->lng->txt("actions"));

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
        $this->setSelectAllCheckbox("usr_id");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.cont_member_skill_row.html", "Services/Container/Skills");

        if (ilContainer::_lookupContainerSetting($this->container_skills->getId(), "cont_skill_publish", 0)) {
            $this->addMultiCommand("publishAssignments", $this->lng->txt("cont_publish_assignment"));
        }
        $this->addMultiCommand("deassignCompetencesConfirm", $this->lng->txt("cont_deassign_competence"));
    }

    /**
     * Get members
     *
     * @param
     * @return
     */
    public function getMembers()
    {
        include_once("./Modules/Course/classes/class.ilCourseParticipants.php");
        $p = ilCourseParticipants::getInstanceByObjId($this->container_skills->getId());

        $members = array();
        foreach ($p->getMembers() as $m) {
            $name = ilObjUser::_lookupName($m);
            $members[] = array(
                "id" => $m,
                "name" => $name["lastname"] . ", " . $name["firstname"],
                "login" => $name["login"],
                "skills" => array()
            );
        }
        return $members;
    }


    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        // levels
        include_once("./Services/Container/Skills/classes/class.ilContainerMemberSkills.php");
        $mskills = new ilContainerMemberSkills($this->container_skills->getId(), $a_set["id"]);
        foreach ($mskills->getOrderedSkillLevels() as $sk) {
            $tpl->setCurrentBlock("level");
            $tpl->setVariable("TXT_SKILL", ilBasicSkill::_lookupTitle($sk["skill_id"], $sk["tref_id"]));
            $tpl->setVariable("TXT_LEVEL", ilBasicSkill::lookupLevelTitle($sk["level_id"]));
            $tpl->setVariable("PATH", $this->getParentObject()->getPathString($sk["skill_id"], $sk["tref_id"]));
            $tpl->parseCurrentBlock();
        }

        // published
        if ($mskills->getPublished()) {
            $tpl->setVariable("PUBLISHED", $lng->txt("yes"));
        } else {
            $tpl->setVariable("PUBLISHED", $lng->txt("no"));
        }


        $tpl->setVariable("NAME", $a_set["name"]);
        $tpl->setVariable("ID", $a_set["id"]);
        $tpl->setVariable("LOGIN", $a_set["login"]);

        $ctrl->setParameter($this->parent_obj, "usr_id", $a_set["id"]);

        $items = array();
        $b = $ui->factory()->button();
        if (!$mskills->getPublished() || (!ilContainer::_lookupContainerSetting($this->container_skills->getId(), "cont_skill_publish", 0))) {
            $items[] = $b->shy($lng->txt("cont_assign_competence"), $ctrl->getLinkTarget($this->parent_obj, "assignCompetences"));
        }
        if (!$mskills->getPublished()) {
            $items[] = $b->shy($lng->txt("cont_publish_assignment"), $ctrl->getLinkTarget($this->parent_obj, "publishAssignments"));
        }
        $items[] = $b->shy($lng->txt("cont_deassign_competence"), $ctrl->getLinkTarget($this->parent_obj, "deassignCompetencesConfirm"));
        $dd = $ui->factory()->dropdown()->standard($items);

        $tpl->setVariable("ACTIONS", $ui->renderer()->render($dd));
    }
}
