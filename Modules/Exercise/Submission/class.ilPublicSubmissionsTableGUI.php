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
 * Exercise member table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPublicSubmissionsTableGUI extends ilTable2GUI
{
    protected ilExAssignment $ass;
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilExAssignment $a_ass
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->ass = $a_ass;
        
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
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     */
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $member_id = $a_set["usr_id"];
        if (($mem_obj = ilObjectFactory::getInstanceByObjId($member_id, false)) === null) {
            return;
        }

        // name and login
        $this->tpl->setVariable(
            "TXT_NAME",
            $a_set["name"]
        );
        $this->tpl->setVariable(
            "TXT_LOGIN",
            "[" . $a_set["login"] . "]"
        );
            
        // image
        $this->tpl->setVariable(
            "USR_IMAGE",
            $mem_obj->getPersonalPicturePath("xxsmall")
        );
        $this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));
        
        $sub = new ilExSubmission($this->ass, $member_id);

        // submission:

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
            $button = ilLinkButton::getInstance();
            $button->setCaption("exc_download_files");
            $button->setUrl($url);
            $button->setOmitPreventDoubleSubmission(true);
            $this->tpl->setVariable("BTN_DOWNLOAD", $button->render());
        }

        $this->tpl->parseCurrentBlock();
    }
}
