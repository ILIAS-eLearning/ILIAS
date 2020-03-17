<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * File-based submissions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilExSubmissionFileGUI:
 * @ingroup ModulesExercise
 */
class ilExSubmissionFileGUI extends ilExSubmissionBaseGUI
{
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilObjUser
     */
    protected $user;


    /**
     * Constructor
     */
    public function __construct(ilObjExercise $a_exercise, ilExSubmission $a_submission)
    {
        global $DIC;

        parent::__construct($a_exercise, $a_submission);

        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC["ilHelp"];
        $this->user = $DIC->user();
    }

    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        if (!$this->submission->canView()) {
            $this->returnToParentObject();
        }
        
        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("submissionScreen");
        
        switch ($class) {
            default:
                $this->{$cmd . "Object"}();
                break;
        }
    }
    
    public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $titles = array();
        foreach ($a_submission->getFiles() as $file) {
            $titles[] = $file["filetitle"];
        }
        $files_str = implode($titles, "<br>");
        if ($files_str == "") {
            $files_str = $lng->txt("message_no_delivered_files");
        }

        // no team == no submission
        if (!$a_submission->hasNoTeamYet()) {
            if ($a_submission->canSubmit()) {
                $title = (count($titles) == 0
                    ? $lng->txt("exc_hand_in")
                    : $lng->txt("exc_edit_submission"));

                $button = ilLinkButton::getInstance();
                $button->setPrimary(true);
                $button->setCaption($title, false);
                $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen"));
                $files_str .= "<br><br>" . $button->render();
            } else {
                if (count($titles) > 0) {
                    $button = ilLinkButton::getInstance();
                    $button->setCaption("already_delivered_files");
                    $button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen"));
                    $files_str .= "<br><br>" . $button->render();
                }
            }
        }

        $a_info->addProperty($lng->txt("exc_files_returned"), $files_str);
    }

    /**
    * Displays a form which allows members to deliver their solutions
    *
    * @access public
    */
    public function submissionScreenObject()
    {
        $ilToolbar = $this->toolbar;
        $ilHelp = $this->help;
        $ilUser = $this->user;


        $this->handleTabs();

        $ilHelp->setScreenIdComponent("exc");
        $ilHelp->setScreenId("submissions");

        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
        } else {
            $max_files = $this->submission->getAssignment()->getMaxFile();
            
            if ($this->submission->canAddFile()) {
                // #15883 - extended deadline warning
                $deadline = $this->assignment->getPersonalDeadline($ilUser->getId());
                if ($deadline &&
                    time() > $deadline) {
                    $dl = ilDatePresentation::formatDate(new ilDateTime($deadline, IL_CAL_UNIX));
                    $dl = sprintf($this->lng->txt("exc_late_submission_warning"), $dl);
                    $dl = '<span class="warning">' . $dl . '</span>';
                    $ilToolbar->addText($dl);
                }
                
                $ilToolbar->addButton(
                    $this->lng->txt("file_add"),
                    $this->ctrl->getLinkTarget($this, "uploadForm")
                );

                if (!$max_files ||
                    $max_files > 1) {
                    $ilToolbar->addButton(
                        $this->lng->txt("header_zip"),
                        $this->ctrl->getLinkTarget($this, "uploadZipForm")
                    );
                }
            }
            
            if ($max_files) {
                ilUtil::sendInfo(sprintf($this->lng->txt("exc_max_file_reached"), $max_files));
            }
        }

        include_once("./Modules/Exercise/classes/class.ilExcDeliveredFilesTableGUI.php");
        $tab = new ilExcDeliveredFilesTableGUI($this, "submissionScreen", $this->submission);
        $this->tpl->setContent($tab->getHTML());
    }
    
    /**
     * Display form for single file upload
     */
    public function uploadFormObject(ilPropertyFormGUI $a_form = null)
    {
        if (!$this->submission->canSubmit()) {
            $this->ctrl->redirect($this, "submissionScreen");
        }
        
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "submissionScreen")
        );

        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("exc");
        $ilHelp->setScreenId("upload_submission");

        if (!$a_form) {
            $a_form = $this->initUploadForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Display form for zip file upload
     */
    public function uploadZipFormObject(ilPropertyFormGUI $a_form = null)
    {
        if (!$this->submission->canSubmit()) {
            $this->ctrl->redirect($this, "submissionScreen");
        }
        
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "submissionScreen")
        );

        if (!$a_form) {
            $a_form = $this->initZipUploadForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }
 
    /**
     * Init upload form form.
     */
    protected function initUploadForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
    
        // file input
        include_once("./Services/Form/classes/class.ilFileWizardInputGUI.php");
        $fi = new ilFileWizardInputGUI($lng->txt("file"), "deliver");
        $fi->setFilenames(array(0 => ''));
        $fi->setRequired(true);
        $form->addItem($fi);
    
        $form->addCommandButton("uploadFile", $lng->txt("upload"));
        $form->addCommandButton("submissionScreen", $lng->txt("cancel"));
                    
        $form->setTitle($lng->txt("file_add"));
        $form->setFormAction($ilCtrl->getFormAction($this, "uploadFile"));
        
        return $form;
    }

    /**
     * Init upload form form.
     */
    protected function initZipUploadForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
    
        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        $fi = new ilFileInputGUI($lng->txt("file"), "deliver");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);
    
        $form->addCommandButton("uploadZip", $lng->txt("upload"));
        $form->addCommandButton("submissionScreen", $lng->txt("cancel"));
                    
        $form->setTitle($lng->txt("header_zip"));
        $form->setFormAction($ilCtrl->getFormAction($this, "uploadZip"));
        
        return $form;
    }
 
    /**
     * Upload files
     */
    public function uploadFileObject()
    {
        $ilCtrl = $this->ctrl;
        
        // #15322
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
        } else {
            $form = $this->initUploadForm();
            if (!$form->checkInput()) {
                return $this->uploadFormObject($form);
            }
            
            $success = false;
            foreach ($_FILES["deliver"]["name"] as $k => $v) {
                $file = array(
                    "name" => $_FILES["deliver"]["name"][$k],
                    "type" => $_FILES["deliver"]["type"][$k],
                    "tmp_name" => $_FILES["deliver"]["tmp_name"][$k],
                    "error" => $_FILES["deliver"]["error"][$k],
                    "size" => $_FILES["deliver"]["size"][$k],
                    );
                if (!$this->submission->uploadFile($file)) {
                    ilUtil::sendFailure($this->lng->txt("exc_upload_error") . " [Single File]", true);
                } else {
                    $success = true;
                }
            }

            if ($success) {
                ilUtil::sendSuccess($this->lng->txt("file_added"), true);
                $this->handleNewUpload();
            }
        }
        
        $ilCtrl->redirect($this, "submissionScreen");
    }

    /**
     * Upload zip file
     */
    public function uploadZipObject()
    {
        $ilCtrl = $this->ctrl;
    
        // #15322
        if (!$this->submission->canSubmit()) {
            ilUtil::sendInfo($this->lng->txt("exercise_time_over"), true);
        } else {
            $form = $this->initZipUploadForm();
            if (!$form->checkInput()) {
                return $this->uploadZipFormObject($form);
            }
            
            if (preg_match("/zip/", $_FILES["deliver"]["type"]) == 1) {
                if ($this->submission->processUploadedZipFile($_FILES["deliver"]["tmp_name"])) {
                    ilUtil::sendSuccess($this->lng->txt("file_added"), true);
                    $this->handleNewUpload();
                }
            }
        }
        
        $ilCtrl->redirect($this, "submissionScreen");
    }
    
    /**
     * Confirm deletion of delivered files
     */
    public function confirmDeleteDeliveredObject()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        if (!$this->submission->canSubmit()) {
            ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }
        
        if (!is_array($_POST["delivered"]) || count($_POST["delivered"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        } else {
            $this->tabs_gui->clearTargets();
            $this->tabs_gui->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "submissionScreen")
            );
        
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("info_delete_sure"));
            $cgui->setCancel($lng->txt("cancel"), "submissionScreen");
            $cgui->setConfirm($lng->txt("delete"), "deleteDelivered");
            
            $files = $this->submission->getFiles();

            foreach ($_POST["delivered"] as $i) {
                reset($files);
                $title = "";
                foreach ($files as $f) {
                    if ($f["returned_id"] == $i) {
                        $title = $f["filetitle"];
                    }
                }
                $cgui->addItem("delivered[]", $i, $title);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete file(s) submitted by user
     *
     * @param
     * @return
     */
    public function deleteDeliveredObject()
    {
        $ilCtrl = $this->ctrl;
        
        if (!$this->submission->canSubmit()) {
            ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
        } elseif (!count($_POST["delivered"])) {
            ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_delete"), true);
        } else {
            $this->submission->deleteSelectedFiles($_POST["delivered"]);
            $this->handleRemovedUpload();
            
            ilUtil::sendSuccess($this->lng->txt("exc_submitted_files_deleted"), true);
        }
        $ilCtrl->redirect($this, "submissionScreen");
    }
    
    /**
     * Download submitted files of user.
     */
    public function downloadReturnedObject($a_only_new = false)
    {
        $lng = $this->lng;

        $peer_review_mask_filename = false;
        
        if ($this->submission->canView()) {
            $peer_review_mask_filename = $this->submission->hasPeerReviewAccess();
        } else {
            // no access
            return;
        }
        
        $this->submission->downloadFiles(null, $a_only_new, $peer_review_mask_filename);
        // we only get here, if no files have been found for download
        if ($a_only_new) {
            ilUtil::sendInfo($lng->txt("exc_all_new_files_offered_already"), true);
        }
        $this->returnToParentObject();
    }

    /**
    * Download newly submitted files of user.
    */
    public function downloadNewReturnedObject()
    {
        $this->downloadReturnedObject(true);
    }

    /**
     * User downloads (own) submitted files
     *
     * @param
     * @return
     */
    public function downloadObject()
    {
        $ilCtrl = $this->ctrl;

        $delivered_id = $_REQUEST["delivered"];

        if (!$this->submission->canView()) {
            $this->returnToParentObject();
        }

        if (!is_array($delivered_id) && $delivered_id > 0) {
            $delivered_id = [$delivered_id];
        }
        if (is_array($delivered_id) && count($delivered_id) > 0) {
            $this->submission->downloadFiles($delivered_id);
            exit;
        } else {
            ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_download"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }
    }
}
