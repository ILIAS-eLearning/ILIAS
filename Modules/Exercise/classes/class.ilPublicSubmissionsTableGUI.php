<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once "Modules/Exercise/classes/class.ilExSubmission.php";

/**
* Exercise member table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilPublicSubmissionsTableGUI extends ilTable2GUI
{
    protected $ass; // [ilExAssignment]
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, ilExAssignment $a_ass)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->ass = $a_ass;
        
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->ass->getMemberListData());
        $this->setTitle($lng->txt("exc_assignment") . ": " . $this->ass->getTitle());
        $this->setTopCommands(true);
        //$this->setLimit(9999);
        
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("exc_submission"), "");
        
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exc_public_submissions_row.html", "Modules/Exercise");
        //$this->disable("footer");
        $this->setEnableTitle(true);
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($member)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once "./Services/Object/classes/class.ilObjectFactory.php";
        $member_id = $member["usr_id"];
        if (!($mem_obj = ilObjectFactory::getInstanceByObjId($member_id, false))) {
            return;
        }

        // name and login
        $this->tpl->setVariable(
            "TXT_NAME",
            $member["name"]
        );
        $this->tpl->setVariable(
            "TXT_LOGIN",
            "[" . $member["login"] . "]"
        );
            
        // image
        $this->tpl->setVariable(
            "USR_IMAGE",
            $mem_obj->getPersonalPicturePath("xxsmall")
        );
        $this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));
        
        $sub = new ilExSubmission($this->ass, $member_id);

        // submission:
        // see if files have been resubmmited after solved
        $last_sub = $sub->getLastSubmission();
        if ($last_sub) {
            $last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub, IL_CAL_DATETIME));
        } else {
            $last_sub = "---";
        }

        // nr of submitted files
        $sub_cnt = count($sub->getFiles());
        
        $this->tpl->setVariable("TXT_SUBMITTED_FILES", $lng->txt("exc_files_returned"));
        $this->tpl->setVariable("VAL_SUBMITTED_FILES", $sub_cnt);
        
        // download command
        if ($sub_cnt > 0) {
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "member_id", $member_id);
            $url = $ilCtrl->getLinkTargetByClass("ilExSubmissionFileGUI", "downloadReturned");
            $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "member_id", "");
            
            // #15126
            include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");
            $button = ilLinkButton::getInstance();
            $button->setCaption("exc_download_files");
            $button->setUrl($url);
            $button->setOmitPreventDoubleSubmission(true);
            $this->tpl->setVariable("BTN_DOWNLOAD", $button->render());
        }

        $this->tpl->parseCurrentBlock();
    }
}
