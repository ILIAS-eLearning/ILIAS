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
 *********************************************************************/

/**
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMBlockedUsersTableGUI extends ilTable2GUI
{
    protected ilObjLearningModule $lm;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjLearningModule $a_lm
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->lm = $a_lm;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->getBlockedUsers());
        $this->setTitle($lng->txt(""));
        
        $this->addColumn("", "", "1px");
        $this->addColumn($this->lng->txt("user"), "user");
        $this->addColumn($this->lng->txt("question"), "");
        $this->addColumn($this->lng->txt("page"), "page_title");
        $this->addColumn($this->lng->txt("cont_last_try"), "last_try");
        $this->addColumn($this->lng->txt("cont_unlocked"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.blocked_users.html", "Modules/LearningModule");

        $this->addMultiCommand("sendMailToBlockedUsers", $lng->txt("send_mail"));
        $this->addMultiCommand("resetNumberOfTries", $lng->txt("cont_reset_nr_of_tries"));
        $this->addMultiCommand("unlockQuestion", $lng->txt("cont_unlock_allow_continue"));
    }

    protected function getBlockedUsers() : array
    {
        /** @var ilLMTracker $track */
        $track = ilLMTracker::getInstance($this->lm->getRefId());

        return $track->getBlockedUsersInformation();
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("USER_QUEST_ID", $a_set["qst_id"] . ":" . $a_set["user_id"]);
        $this->tpl->setVariable("USER_NAME", $a_set["user_name"]);
        $this->tpl->setVariable("QUESTION", $a_set["question_text"]);
        $this->tpl->setVariable("PAGE", $a_set["page_title"]);
        $this->tpl->setVariable("LAST_TRY", $a_set["last_try"]);
        $this->tpl->setVariable("IGNORE_FAIL", ($a_set["unlocked"] ? $lng->txt("yes") : $lng->txt("no")));
    }
}
