<?php

declare(strict_types=1);

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

use ILIAS\Container\Skills\ContainerSkillManager;

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
    protected ContainerSkillManager $cont_skill_manager;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilContainer $cont_obj
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->cont_skill_manager = $DIC->skills()->internalContainer()->manager()->getSkillManager(
            $cont_obj->getId(),
            $cont_obj->getRefId()
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
        $skills = $this->cont_skill_manager->getSkillsForTableGUI();

        return $skills;
    }

    protected function fillRow(array $a_set): void
    {
        $tpl = $this->tpl;

        $tpl->setVariable("TITLE", $a_set["title"]);

        $path = $this->getParentObject()->getPathString($a_set["base_skill_id"], $a_set["tref_id"]);
        $tpl->setVariable("PATH", $path);

        if (isset($a_set["profile_title"])) {
            $tpl->setVariable("PROFILE", $a_set["profile_title"]);
        } else {
            $tpl->setCurrentBlock("checkbox");
            $tpl->setVariable("ID", $a_set["base_skill_id"] . ":" . $a_set["tref_id"]);
            $tpl->parseCurrentBlock();
        }
    }
}
