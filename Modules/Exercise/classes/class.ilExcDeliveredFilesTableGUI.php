<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Delivered files table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilExcDeliveredFilesTableGUI extends ilTable2GUI
{
    protected $submission; // [ilExSubmission]
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, ilExSubmission $a_submission)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
            
        $this->submission = $a_submission;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->submission->getFiles());
        $this->setTitle($this->lng->txt("already_delivered_files") . " - " .
            $this->submission->getAssignment()->getTitle());
        $this->setLimit(9999);
        
        $this->addColumn($this->lng->txt(""), "", "1", 1);
        $this->addColumn($this->lng->txt("filename"), "filetitle");
        
        if ($this->submission->getAssignment()->getAssignmentType()->usesTeams() &&
            $this->submission->getAssignment()->getAssignmentType()->usesFileUpload()) {
            // #11957
            $this->lng->loadLanguageModule("file");
            $this->addColumn($this->lng->txt("file_uploaded_by"));
            include_once "Services/User/classes/class.ilUserUtil.php";
        }
        
        $this->addColumn($this->lng->txt("date"), "timestamp14");
        
        if ($this->submission->getAssignment()->getExtendedDeadline()) {
            $this->addColumn($this->lng->txt("exc_late_submission"), "late");
        }
        
        $this->addColumn($this->lng->txt("action"));
        $this->setDefaultOrderField("filetitle");
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.delivered_file_row.html", "Modules/Exercise");
        $this->disable("footer");
        $this->setEnableTitle(true);

        if ($this->submission->canSubmit()) {
            $this->addMultiCommand("confirmDeleteDelivered", $lng->txt("delete"));
        }
    }

    /**
    * Fill table row
    */
    protected function fillRow($file)
    {
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("FILE_ID", $file["returned_id"]);
        $this->tpl->setVariable("DELIVERED_FILE", $file["filetitle"]);
                
        $date = new ilDateTime($file['timestamp14'], IL_CAL_TIMESTAMP);
        $this->tpl->setVariable("DELIVERED_DATE", ilDatePresentation::formatDate($date));
        
        if ($this->submission->getAssignment()->getAssignmentType()->usesTeams() &&
            $this->submission->getAssignment()->getAssignmentType()->usesFileUpload()) {
            $this->tpl->setVariable(
                "DELIVERED_OWNER",
                ilUserUtil::getNamePresentation($file["owner_id"])
            );
        }
        
        if ($this->submission->getAssignment()->getExtendedDeadline()) {
            $this->tpl->setVariable("DELIVERED_LATE", ($file["late"])
                ? '<span class="warning">' . $this->lng->txt("yes") . '</span>'
                : $this->lng->txt("no"));
        }
        
        // #16164 - download
        $ilCtrl->setParameter($this->getParentObject(), "delivered", $file["returned_id"]);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), "download");
        $ilCtrl->setParameter($this->getParentObject(), "delivered", "");
        $this->tpl->setVariable("ACTION_TXT", $this->lng->txt("download"));
        $this->tpl->setVariable("ACTION_URL", $url);
    }
}
