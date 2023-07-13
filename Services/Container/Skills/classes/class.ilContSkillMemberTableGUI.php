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

use ILIAS\DI\UIServices;
use ILIAS\Container\Skills\ContainerSkillManager;

/**
 * TableGUI class for container members / skill assignments
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilContSkillMemberTableGUI extends ilTable2GUI
{
    protected ilContainer $cont_obj;
    protected UIServices $ui;
    protected ContainerSkillManager $cont_skill_manager;

    public function __construct(ilContSkillAdminGUI $a_parent_obj, string $a_parent_cmd, ilContainer $cont_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ui = $DIC->ui();

        $this->cont_obj = $cont_obj;
        $this->setId("cont_skll_mem_" . $this->cont_obj->getId());
        $this->cont_skill_manager = $DIC->skills()->internalContainer()->manager()->getSkillManager(
            $this->cont_obj->getId(),
            $this->cont_obj->getRefId()
        );

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
        $this->setSelectAllCheckbox("usr_ids");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.cont_member_skill_row.html", "Services/Container/Skills");

        if (ilContainer::_lookupContainerSetting($this->cont_obj->getId(), "cont_skill_publish", '0')) {
            $this->addMultiCommand("publishAssignments", $this->lng->txt("cont_publish_assignment"));
        }
        $this->addMultiCommand("deassignCompetencesConfirm", $this->lng->txt("cont_deassign_competence"));
    }

    public function getMembers(): array
    {
        $p = ilCourseParticipants::getInstanceByObjId($this->cont_obj->getId());

        $members = [];
        foreach ($p->getMembers() as $m) {
            $name = ilObjUser::_lookupName($m);
            $members[] = [
                "id" => $m,
                "name" => $name["lastname"] . ", " . $name["firstname"],
                "login" => $name["login"],
                "skills" => []
            ];
        }
        return $members;
    }

    protected function fillRow(array $a_set): void
    {
        $tpl = $this->tpl;
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        $user_id = (int) $a_set["id"];

        // levels
        foreach ($this->cont_skill_manager->getMemberSkillLevelsForContainerOrdered($user_id) as $sk) {
            $tpl->setCurrentBlock("level");
            $tpl->setVariable("TXT_SKILL", ilBasicSkill::_lookupTitle($sk->getBaseSkillId(), $sk->getTrefId()));
            $tpl->setVariable("TXT_LEVEL", ilBasicSkill::lookupLevelTitle($sk->getLevelId()));
            $tpl->setVariable("PATH", $this->getParentObject()->getPathString($sk->getBaseSkillId(), $sk->getTrefId()));
            $tpl->parseCurrentBlock();
        }

        // published
        if ($this->cont_skill_manager->getPublished($user_id)) {
            $tpl->setVariable("PUBLISHED", $lng->txt("yes"));
        } else {
            $tpl->setVariable("PUBLISHED", $lng->txt("no"));
        }


        $tpl->setVariable("NAME", $a_set["name"]);
        $tpl->setVariable("ID", $a_set["id"]);
        $tpl->setVariable("LOGIN", $a_set["login"]);

        $ctrl->setParameter($this->parent_obj, "usr_id", $a_set["id"]);

        $items = [];
        $b = $ui->factory()->button();
        if (!$this->cont_skill_manager->getPublished($user_id) || (!ilContainer::_lookupContainerSetting($this->cont_obj->getId(), "cont_skill_publish", '0'))) {
            $items[] = $b->shy($lng->txt("cont_assign_competence"), $ctrl->getLinkTarget($this->parent_obj, "assignCompetences"));
        }
        if (!$this->cont_skill_manager->getPublished($user_id)) {
            $items[] = $b->shy($lng->txt("cont_publish_assignment"), $ctrl->getLinkTarget($this->parent_obj, "publishAssignments"));
        }
        $items[] = $b->shy($lng->txt("cont_deassign_competence"), $ctrl->getLinkTarget($this->parent_obj, "deassignCompetencesConfirm"));
        $dd = $ui->factory()->dropdown()->standard($items);

        $tpl->setVariable("ACTIONS", $ui->renderer()->render($dd));
    }
}
