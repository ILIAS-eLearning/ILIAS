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
 * Self evaluation, second simplier implementation
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSelfEvaluationSimpleTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected int $top_skill_id = 0;
    protected int $tref_id = 0;
    protected int $basic_skill_id = 0;
    protected int $cur_level_id = 0;
    protected ilSkillTreeNode $skill;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        int $a_top_skill_id,
        int $a_tref_id,
        int $a_basic_skill_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->user = $DIC->user();

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        
        $this->top_skill_id = $a_top_skill_id;
        $this->tref_id = $a_tref_id;
        $this->basic_skill_id = $a_basic_skill_id;

        $this->cur_level_id = ilPersonalSkill::getSelfEvaluation(
            $ilUser->getId(),
            $this->top_skill_id,
            $this->tref_id,
            $this->basic_skill_id
        );

        // build title
        $tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $tree_id = $tree_repo->getTreeIdForNodeId($this->basic_skill_id);
        $node_manager = $DIC->skills()->internal()->manager()->getTreeNodeManager($tree_id);
        $title = $node_manager->getWrittenPath($this->basic_skill_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getLevels());
        $this->setTitle($title);
        $this->setLimit(9999);
        
        $this->addColumn("", "", "", true);
        $this->addColumn($this->lng->txt("skmg_skill_level"));
        $this->addColumn($this->lng->txt("description"));
        
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.simple_self_eval.html", "Services/Skill");
        $this->disable("footer");
        $this->setEnableTitle(true);
        
        $this->addCommandButton(
            "saveSelfEvaluation",
            $lng->txt("save")
        );
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
    }

    public function getLevels() : array
    {
        $lng = $this->lng;

        $this->skill = ilSkillTreeNodeFactory::getInstance($this->basic_skill_id);
        $levels[] = array("id" => 0, "title" => "", "description" => $lng->txt("skmg_no_skills"));
        foreach ($this->skill->getLevelData() as $k => $v) {
            $levels[] = $v;
        }

        return $levels;
    }

    protected function fillRow(array $a_set) : void
    {
        if ($this->cur_level_id == $a_set["id"]) {
            $this->tpl->setVariable("CHECKED", "checked='checked'");
        }
        
        $this->tpl->setVariable("LEVEL_ID", $a_set["id"]);
        $this->tpl->setVariable("SKILL_ID", $this->basic_skill_id);
        $this->tpl->setVariable("TXT_SKILL", $a_set["title"]);
        $this->tpl->setVariable("TXT_SKILL_DESC", $a_set["description"]);
    }
}
