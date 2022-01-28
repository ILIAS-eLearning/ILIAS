<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFeedbackConfirmationTable2GUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected ilExAssignment $ass;

    /**
     * Constructor
     */
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilExAssignment $a_ass
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $ilUser = $DIC->user();
        
        $this->ass = $a_ass;
        $this->setId("exc_mdf_upload");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $this->setLimit(9999);
        $this->setData($this->ass->getMultiFeedbackFiles($ilUser->getId()));
        $this->setTitle($lng->txt("exc_multi_feedback_files"));
        $this->setSelectAllCheckbox("file[]");
        
        $this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("lastname"), "lastname");
        $this->addColumn($this->lng->txt("firstname"), "firstname");
        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("file"), "file");
        
        $this->setFormAction($ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.multi_feedback_confirmation_row.html", "Modules/Exercise");

        $this->addCommandButton("saveMultiFeedback", $lng->txt("save"));
        $this->addCommandButton("cancelMultiFeedback", $lng->txt("cancel"));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("FILE", $a_set["file"]);
        $this->tpl->setVariable("POST_FILE", md5($a_set["file"]));
        $this->tpl->setVariable("USER_ID", $a_set["user_id"]);
    }
}
