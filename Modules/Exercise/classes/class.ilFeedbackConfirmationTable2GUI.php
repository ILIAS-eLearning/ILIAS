<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilFeedbackConfirmationTable2GUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_ass)
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
        
        $this->ass = $a_ass;
        $this->setId("exc_mdf_upload");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);
        $this->setData($this->ass->getMultiFeedbackFiles($ilUser->getId()));
        $this->setTitle($lng->txt("exc_multi_feedback_files"));
        $this->setSelectAllCheckbox("file[]");
        
        $this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("lastname"), "lastname");
        $this->addColumn($this->lng->txt("firstname"), "firstname");
        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("file"), "file");
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.multi_feedback_confirmation_row.html", "Modules/Exercise");

        $this->addCommandButton("saveMultiFeedback", $lng->txt("save"));
        $this->addCommandButton("cancelMultiFeedback", $lng->txt("cancel"));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        $this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("FILE", $a_set["file"]);
        $this->tpl->setVariable("POST_FILE", md5($a_set["file"]));
        $this->tpl->setVariable("USER_ID", $a_set["user_id"]);
    }
}
