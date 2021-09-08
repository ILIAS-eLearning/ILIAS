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
 * Self evaluation overview table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSelfEvaluationTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    protected ilAccessHandler $access;
    protected ilObjUser $user;

    public function __construct($a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData(ilSkillSelfEvaluation::getAllSelfEvaluationsOfUser($ilUser->getId()));
        $this->setTitle($lng->txt("skmg_self_evaluations"));

        $this->addColumn("", "", 1);
        $this->addColumn($this->lng->txt("created"));
        $this->addColumn($this->lng->txt("last_update"));
        $this->addColumn($this->lng->txt("skmg_skill"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.self_eval_overview_row.html", "Services/Skill");
        $this->setEnableTitle(true);
        
        $this->addMultiCommand("confirmSelfEvaluationDeletion", $lng->txt("delete"));
        //$this->addCommandButton("", $lng->txt(""));
    }

    protected function fillRow($a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("SE_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_CREATED", $a_set["created"]);
        $this->tpl->setVariable("VAL_LAST_UPDATE", $a_set["last_update"]);
        $this->tpl->setVariable(
            "VAL_SKILL",
            ilSkillTreeNode::_lookupTitle($a_set["top_skill_id"])
        );
        $this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
        $ilCtrl->setParameter($this->parent_obj, "se_id", $a_set["id"]);
        $this->tpl->setVariable(
            "HREF_CMD",
            $ilCtrl->getLinkTarget($this->parent_obj, "editSelfEvaluation")
        );
    }
}
