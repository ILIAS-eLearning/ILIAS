<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Container skills administration
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesContainer
 */
class ilContSkillAdminGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilContainerGUI
     */
    protected $container_gui;

    /**
     * @var ilContainer
     */
    protected $container;

    /**
     * @var ilContainerSkills
     */
    protected $container_skills;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct($a_container_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();

        $this->container_gui = $a_container_gui;
        $this->container = $a_container_gui->object;
        $this->ref_id = $this->container->getRefId();

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $this->skill_tree = new ilSkillTree();

        include_once("./Services/Container/Skills/classes/class.ilContainerSkills.php");
        $this->container_skills = new ilContainerSkills($this->container->getId());

        $this->user_id = (int) $_GET["usr_id"];
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listMembers");
    
        switch ($next_class) {
            default:
                if (
                    ($this->access->checkAccess("write", "", $this->ref_id) &&
                        in_array($cmd, array("listCompetences", "settings", "saveSettings", "selectSkill",
                        "saveSelectedSkill", "confirmRemoveSelectedSkill", "removeSelectedSkill")))
                    ||
                    ($this->access->checkAccess("grade", "", $this->ref_id) &&
                        in_array($cmd, array("listMembers", "assignCompetences",
                            "saveCompetenceAssignment", "publishAssignments", "deassignCompetencesConfirm", "deassignCompetences")))
                ) {
                    $this->$cmd();
                }
        }
    }

    //// MANAGE MEMBERS

    /**
     * List members
     */
    public function listMembers()
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("members");

        // table
        include_once("./Services/Container/Skills/classes/class.ilContSkillMemberTableGUI.php");
        $tab = new ilContSkillMemberTableGUI($this, "listMembers", $this->container_skills);

        $tpl->setContent($tab->getHTML());
    }

    /**
     * Assign competences to a member
     */
    public function assignCompetences()
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;
        $ctrl = $this->ctrl;

        $ctrl->saveParameter($this, "usr_id");
        $tabs->activateSubTab("members");

        $form = $this->initCompetenceAssignmentForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init competence assignment form
     */
    public function initCompetenceAssignmentForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        include_once("./Services/Container/Skills/classes/class.ilContainerMemberSkills.php");
        $mem_skills = new ilContainerMemberSkills($this->container_skills->getId(), $this->user_id);
        $mem_levels = $mem_skills->getSkillLevels();

        // user name
        $name = ilObjUser::_lookupName($this->user_id);
        $ne = new ilNonEditableValueGUI($this->lng->txt("obj_user"), "");
        $ne->setValue($name["lastname"] . ", " . $name["firstname"] . " [" . $name["login"] . "]");
        $form->addItem($ne);

        foreach ($this->container_skills->getOrderedSkills() as $sk) {
            $skill = new ilBasicSkill($sk["skill_id"]);

            // skill level options
            $options = array(
                "-1" => $this->lng->txt("cont_skill_do_not_set"),
                );
            foreach ($skill->getLevelData() as $l) {
                $options[$l["id"]] = $l["title"];
            }
            $si = new ilSelectInputGUI(ilBasicSkill::_lookupTitle($sk["skill_id"], $sk["tref_id"]), "skill_" . $sk["skill_id"] . "_" . $sk["tref_id"]);
            $si->setOptions($options);
            $si->setInfo($this->getPathString($sk["skill_id"], $sk["tref_id"]));
            if (isset($mem_levels[$sk["skill_id"] . ":" . $sk["tref_id"]])) {
                $si->setValue($mem_levels[$sk["skill_id"] . ":" . $sk["tref_id"]]);
            }
            $form->addItem($si);
        }

        // save and cancel commands
        $form->addCommandButton("saveCompetenceAssignment", $this->lng->txt("save"));
        $form->addCommandButton("listMembers", $this->lng->txt("cancel"));

        $form->setTitle($this->lng->txt("cont_assign_skills"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Get path string
     *
     * @return string path string
     */
    public function getPathString($a_skill_id, $a_tref_id = 0)
    {
        $skill_tree = $this->skill_tree;

        $path = $skill_tree->getSkillTreePath($a_skill_id, $a_tref_id);
        $titles = array();
        foreach ($path as $v) {
            if ($v["type"] != "skrt" && !($v["skill_id"] == $a_skill_id && $v["tref_id"] == $a_tref_id)) {
                $titles[] = $v["title"];
            }
        }

        return implode(" > ", $titles);
    }

    /**
     * Save competence assignment
     *
     * @param
     */
    public function saveCompetenceAssignment()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $form = $this->initCompetenceAssignmentForm();
        $form->checkInput();

        $levels = array();
        foreach ($this->container_skills->getSkills() as $sk) {
            $l = $form->getInput("skill_" . $sk["skill_id"] . "_" . $sk["tref_id"]);
            if ($l != -1) {
                $levels[$sk["skill_id"] . ":" . $sk["tref_id"]] = $l;
            }
        }

        include_once("./Services/Container/Skills/classes/class.ilContainerMemberSkills.php");
        $mem_skills = new ilContainerMemberSkills($this->container_skills->getId(), $this->user_id);
        $mem_skills->saveLevelForSkills($levels);

        if (!ilContainer::_lookupContainerSetting($this->container->getId(), "cont_skill_publish", 0)) {
            $mem_skills->publish($this->container->getRefId());
        }

        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ctrl->redirect($this, "listMembers");
    }

    /**
     * Publish assignments
     */
    public function publishAssignments()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $user_ids = $_POST["usr_id"];
        if (!is_array($_POST["usr_id"]) && $_GET["usr_id"] > 0) {
            $user_ids[] = $_GET["usr_id"];
        }

        include_once("./Services/Container/Skills/classes/class.ilContainerMemberSkills.php");
        $not_changed = array();
        foreach ($user_ids as $user_id) {
            $mem_skills = new ilContainerMemberSkills($this->container_skills->getId(), $user_id);
            if (!$mem_skills->publish($this->container->getRefId())) {
                $not_changed[] = $user_id;
            }
        }

        if (count($not_changed) == 0) {
            ilUtil::sendSuccess($lng->txt("cont_skll_published"), true);
        } else {
            $names = array_map(function ($id) {
                return ilUserUtil::getNamePresentation($id, false, false, "", true);
            }, $not_changed);
            ilUtil::sendInfo($lng->txt("cont_skll_published_some_not") . " (" . implode("; ", $names) . ")", true);
        }


        $ctrl->redirect($this, "listMembers");
    }

    /**
     * Deassign competences confirmation
     */
    public function deassignCompetencesConfirm()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("members");

        $user_ids = $_POST["usr_id"];
        if (!is_array($_POST["usr_id"]) && $_GET["usr_id"] > 0) {
            $user_ids[] = $_GET["usr_id"];
        }

        if (!is_array($user_ids) || count($user_ids) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "listMembers");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_deassign_skills"));
            $cgui->setCancel($lng->txt("cancel"), "listMembers");
            $cgui->setConfirm($lng->txt("cont_deassign_competence"), "deassignCompetences");

            foreach ($user_ids as $i) {
                $name = ilUserUtil::getNamePresentation($i, false, false, "", true);
                $cgui->addItem("usr_id[]", $i, $name);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Deassign competences
     */
    public function deassignCompetences()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        foreach ($_POST["usr_id"] as $user_id) {
            include_once("./Services/Container/Skills/classes/class.ilContainerMemberSkills.php");
            $mem_skills = new ilContainerMemberSkills($this->container_skills->getId(), $user_id);
            $mem_skills->removeAllSkillLevels($this->container->getRefId());
        }

        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ctrl->redirect($this, "listMembers");
    }


    //// MANAGE COMPETENCES

    /**
     * Select competences
     */
    public function listCompetences()
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;
        $toolbar = $this->toolbar;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tabs->activateSubTab("competences");

        $toolbar->addButton(
            $lng->txt("cont_add_skill"),
            $ctrl->getLinkTarget($this, "selectSkill")
        );

        // table
        include_once("./Services/Container/Skills/classes/class.ilContSkillTableGUI.php");
        $tab = new ilContSkillTableGUI($this, "listCompetences", $this->container_skills);

        $tpl->setContent($tab->getHTML());
    }

    /**
     * Select skill for container
     */
    public function selectSkill()
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("competences");

        include_once("./Services/Skill/classes/class.ilSkillSelectorGUI.php");
        $sel = new ilSkillSelectorGUI($this, "selectSkill", $this, "saveSelectedSkill");
        if (!$sel->handleCommand()) {
            $tpl->setContent($sel->getHTML());
        }
    }

    /**
     * Save selected skill
     */
    public function saveSelectedSkill()
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $s = explode(":", ($_GET["selected_skill"]));

        $this->container_skills->addSkill((int) $s[0], (int) $s[1]);
        $this->container_skills->save();

        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listCompetences");
    }

    /**
     * Confirm
     */
    public function confirmRemoveSelectedSkill()
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "listCompetences");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ctrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_remove_skill_from_course"));
            $cgui->setCancel($lng->txt("cancel"), "listCompetences");
            $cgui->setConfirm($lng->txt("remove"), "removeSelectedSkill");

            foreach ($_POST["id"] as $i) {
                $s = explode(":", $i);
                $cgui->addItem("id[]", $i, ilBasicSkill::_lookupTitle((int) $s[0], (int) $s[1]));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Remove skill from course selection
     */
    public function removeSelectedSkill()
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        
        if (is_array($_POST["id"]) && count($_POST["id"]) > 0) {
            foreach ($_POST["id"] as $id) {
                $s = explode(":", $id);
                $this->container_skills->removeSkill($s[0], $s[1]);
            }
            $this->container_skills->save();
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "listCompetences");
    }


    //// SETTINGS

    /**
     * Settings
     */
    public function settings()
    {
        $tpl = $this->tpl;
        $tabs = $this->tabs;

        $tabs->activateSubTab("settings");

        $form = $this->initSettingsForm();

        $tpl->setContent($form->getHTML());
    }

    /**
     * Init settings form.
     */
    public function initSettingsForm()
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // publish
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_skill_publish"), "cont_skill_publish");
        $op1 = new ilRadioOption($lng->txt("cont_skill_publish_auto"), 0, $lng->txt("cont_skill_publish_auto_info"));
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("cont_skill_publish_manual"), 1, $lng->txt("cont_skill_publish_manual_info"));
        $radg->addOption($op2);
        $form->addItem($radg);
        $radg->setValue(ilContainer::_lookupContainerSetting($this->container->getId(), "cont_skill_publish", 0));

        $form->addCommandButton("saveSettings", $lng->txt("save"));

        $form->setTitle($lng->txt("settings"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Save settings
     */
    public function saveSettings()
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $form = $this->initSettingsForm();
        $form->checkInput();
        ilContainer::_writeContainerSetting($this->container->getId(), "cont_skill_publish", $form->getInput("cont_skill_publish"));

        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

        $ctrl->redirect($this, "settings");
    }
}
