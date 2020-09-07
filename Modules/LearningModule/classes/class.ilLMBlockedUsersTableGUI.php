<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilLMBlockedUsersTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_lm)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
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
        //$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Get blocked users
     *
     * @return array array of blocked user information
     */
    protected function getBlockedUsers()
    {
        include_once("./Modules/LearningModule/classes/class.ilLMTracker.php");
        /** @var ilLMTracker $track */
        $track = ilLMTracker::getInstance($this->lm->getRefId());

        return $bl_users = $track->getBlockedUsersInformation();
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
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
