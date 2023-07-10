<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * TableGUI class for competences in containers
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilContSkillTableGUI extends ilTable2GUI
{
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    protected ilContainerSkills $container_skills;
    protected ilContainerGlobalProfiles $container_global_profiles;
    protected ilContainerLocalProfiles $container_local_profiles;
    protected ilContSkillCollector $container_skill_collector;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilContainerSkills $a_cont_skills,
        ilContainerGlobalProfiles $a_cont_glb_profiles,
        ilContainerLocalProfiles $a_cont_lcl_profiles
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

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

    public function getSkills(): array
    {
        $skills = $this->container_skill_collector->getSkillsForTableGUI();

        return $skills;
    }

    protected function fillRow(array $a_set): void
    {
        $tpl = $this->tpl;

        $tpl->setVariable("TITLE", $a_set["title"]);

        $path = $this->getParentObject()->getPathString($a_set["base_skill_id"], $a_set["tref_id"]);
        $tpl->setVariable("PATH", $path);

        if (isset($a_set["profile"])) {
            $tpl->setVariable("PROFILE", $a_set["profile"]);
        } else {
            $tpl->setCurrentBlock("checkbox");
            $tpl->setVariable("ID", $a_set["base_skill_id"] . ":" . $a_set["tref_id"]);
            $tpl->parseCurrentBlock();
        }
    }
}
