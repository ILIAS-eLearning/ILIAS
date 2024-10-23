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

use ILIAS\DI\UIServices;

/**
 * File-based submissions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilExSubmissionFileGUI: ilRepoStandardUploadHandlerGUI
 */
class ilExSubmissionFileGUI extends ilExSubmissionBaseGUI
{
    protected \ILIAS\Exercise\Submission\SubmissionManager $subm;
    protected $log;
    protected ilToolbarGUI $toolbar;
    protected ilHelpGUI $help;
    protected ilObjUser $user;
    protected UIServices $ui;

    public function __construct(
        ilObjExercise $a_exercise,
        ilExSubmission $a_submission
    ) {
        global $DIC;

        parent::__construct($a_exercise, $a_submission);

        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC["ilHelp"];
        $this->user = $DIC->user();
        $this->ui = $DIC->ui();
        $this->log = $DIC->logger()->exc();
        $this->subm = $DIC->exercise()->internal()->domain()->submission(
            $a_submission->getAssignment()->getId()
        );
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->submission->canView()) {
            $this->returnToParentObject();
        }

        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("submissionScreen");

        switch ($class) {
            case strtolower(ilRepoStandardUploadHandlerGUI::class):
                if ($this->request->getZip()) {
                    $form = $this->getZipUploadForm();
                } else {
                    $form = $this->getUploadForm();
                }
                $gui = $form->getRepoStandardUploadHandlerGUI("deliver");
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->{$cmd . "Object"}();
                break;
        }
    }

    public static function getOverviewContent(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission
    ): void {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $gui = $DIC->exercise()->internal()->gui();
        $subm = $DIC->exercise()->internal()->domain()->submission(
            $a_submission->getAssignment()->getId()
        );

        $titles = array();
        foreach ($subm->getSubmissionsOfUser($a_submission->getUserId()) as $sub) {
            $titles[] = htmlentities($sub->getTitle());
        }
        $files_str = implode("<br>", $titles);
        if ($files_str == "") {
            $files_str = $lng->txt("message_no_delivered_files");
        }

        // no team == no submission
        if (!$a_submission->hasNoTeamYet()) {
            if ($a_submission->canSubmit()) {
                $title = (count($titles) == 0
                    ? $lng->txt("exc_hand_in")
                    : $lng->txt("exc_edit_submission"));

                $button = $gui->link(
                    $title,
                    $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen")
                )->primary();
                $files_str .= "<br><br>" . $button->render();
            } else {
                if (count($titles) > 0) {
                    $link = $gui->link(
                        $lng->txt("already_delivered_files"),
                        $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen")
                    )->emphasised();
                    $files_str .= "<br><br>" . $link->render();
                }
            }
        }

        $a_info->addProperty($lng->txt("exc_files_returned"), $files_str);
    }

    // Displays a form which allows members to deliver their solutions
    public function submissionScreenObject(): void
    {
        $ilToolbar = $this->toolbar;
        $ilHelp = $this->help;
        $ilUser = $this->user;

        $this->triggerAssignmentTool();

        $this->handleTabs();

        $ilHelp->setScreenIdComponent("exc");
        $ilHelp->setScreenId("submissions");

        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("exercise_time_over"));
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

                $b = $this->ui->factory()->button()->standard(
                    $this->lng->txt("file_add"),
                    $this->ctrl->getLinkTarget($this, "uploadForm")
                );
                $ilToolbar->addStickyItem($b);

                if (!$max_files ||
                    $max_files > 1) {
                    $ilToolbar->addButton(
                        $this->lng->txt("header_zip"),
                        $this->ctrl->getLinkTarget($this, "uploadZipForm")
                    );
                }
            }

            if ($max_files) {
                $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("exc_max_file_reached"), $max_files));
            }
        }

        $tab = new ilExcDeliveredFilesTableGUI($this, "submissionScreen", $this->submission);
        $this->tpl->setContent($tab->getHTML());
    }

    // Display form for single file upload
    public function uploadFormObject(
        ilPropertyFormGUI $a_form = null
    ): void {
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
            $a_form = $this->getUploadForm();
            $this->tpl->setContent($a_form->render());
            return;
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function uploadZipFormObject(
        \ILIAS\Repository\Form\FormAdapterGUI $a_form = null
    ): void {
        if (!$this->submission->canSubmit()) {
            $this->ctrl->redirect($this, "submissionScreen");
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "submissionScreen")
        );

        if (!$a_form) {
            $a_form = $this->getZipUploadForm();
        }
        $this->tpl->setContent($a_form->render());
    }

    /**
     * Init upload form form.
     */
    protected function initUploadForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        // file input
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

    protected function getUploadForm(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        $max_file = $this->submission->getAssignment()->getMaxFile();
        $cnt_sub = $this->subm->countSubmissionsOfUser($this->submission->getUserId());
        if ($max_file > 0) {
            $max_file = $this->submission->getAssignment()->getMaxFile() - $cnt_sub;
        } else {
            $max_file = 20;
        }

        $form_adapter = $this->gui
            ->form(self::class, 'addUpload')
            ->section("props", $this->lng->txt('file_add'))
            ->file(
                "deliver",
                $this->lng->txt("files"),
                $this->handleUploadResult(...),
                "mep_id",
                "",
                $max_file
            );
        return $form_adapter;
    }

    protected function handleUploadResult(
        \ILIAS\FileUpload\FileUpload $upload,
        \ILIAS\FileUpload\DTO\UploadResult $result
    ): \ILIAS\FileUpload\Handler\BasicHandlerResult {
        $title = $result->getName();

        //$this->submission->addFileUpload($result);
        $subm = $this->domain->submission($this->assignment->getId());
        $subm->addUpload(
            $this->user->getid(),
            $result,
            $title
        );

        return new \ILIAS\FileUpload\Handler\BasicHandlerResult(
            '',
            \ILIAS\FileUpload\Handler\HandlerResult::STATUS_OK,
            $title,
            ''
        );
    }

    public function addUploadObject(): void
    {
        $ilCtrl = $this->ctrl;
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("file_added"), true);
        $this->handleNewUpload();

        $ilCtrl->redirect($this, "submissionScreen");
    }


    /**
     * Init upload form form.
     */
    /*
    protected function initZipUploadForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        $fi = new ilFileInputGUI($lng->txt("file"), "deliver");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("uploadZip", $lng->txt("upload"));
        $form->addCommandButton("submissionScreen", $lng->txt("cancel"));

        $form->setTitle($lng->txt("header_zip"));
        $form->setFormAction($ilCtrl->getFormAction($this, "uploadZip"));

        return $form;
    }*/

    protected function getZipUploadForm(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        /*
        $max_file = $this->submission->getAssignment()->getMaxFile();
        if ($max_file > 0) {
            $max_file = $this->submission->getAssignment()->getMaxFile() - count($this->submission->getFiles());
        } else {
            $max_file = 20;
        }*/
        $this->ctrl->setParameterByClass(self::class, "zip", "1");
        $form_adapter = $this->gui
            ->form(self::class, 'addUpload')
            ->section("props", $this->lng->txt('file_add'))
            ->file(
                "deliver",
                $this->lng->txt("files"),
                \Closure::fromCallable([$this, 'handleZipUploadResult']),
                "mep_id",
                "",
                1,
                ["application/zip"]
            );
        return $form_adapter;
    }



    protected function handleZipUploadResult(
        \ILIAS\FileUpload\FileUpload $upload,
        \ILIAS\FileUpload\DTO\UploadResult $result
    ): \ILIAS\FileUpload\Handler\BasicHandlerResult {
        $title = $result->getName();
        //$this->submission->addFileUpload($result);
        $subm = $this->domain->submission($this->assignment->getId());
        if ($this->submission->canSubmit()) {
            $this->log->debug("-------------------------------------");
            $subm->addZipUploads(
                $this->user->getid(),
                $result
            );
        }

        return new \ILIAS\FileUpload\Handler\BasicHandlerResult(
            '',
            \ILIAS\FileUpload\Handler\HandlerResult::STATUS_OK,
            $title,
            ''
        );
    }

    /**
     * Confirm deletion of delivered files
     */
    public function confirmDeleteDeliveredObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $file_ids = $this->request->getSubmittedFileIds();
        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exercise_time_over"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }

        if (count($file_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        } else {
            $this->tabs_gui->clearTargets();
            $this->tabs_gui->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "submissionScreen")
            );

            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("info_delete_sure"));
            $cgui->setCancel($lng->txt("cancel"), "submissionScreen");
            $cgui->setConfirm($lng->txt("delete"), "deleteDelivered");

            $subs = $this->subm->getSubmissionsOfUser(
                $this->submission->getUserId(),
                $file_ids
            );

            foreach ($subs as $sub) {
                $cgui->addItem("delivered[]", $sub->getId(), $sub->getTitle());
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete file(s) submitted by user
     */
    public function deleteDeliveredObject(): void
    {
        $ilCtrl = $this->ctrl;

        $file_ids = $this->request->getSubmittedFileIds();

        if (!$this->submission->canSubmit()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exercise_time_over"), true);
        } elseif (count($file_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("please_select_a_delivered_file_to_delete"), true);
        } else {
            $this->subm->deleteSubmissions(
                $this->submission->getUserId(),
                $file_ids
            );
            $this->handleRemovedUpload();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_submitted_files_deleted"), true);
        }
        $ilCtrl->redirect($this, "submissionScreen");
    }

    /**
     * Download submitted files of user.
     */
    public function downloadReturnedObject(bool $a_only_new = false): void
    {
        $lng = $this->lng;

        if ($this->submission->canView()) {
            $peer_review_mask_filename = $this->submission->hasPeerReviewAccess();
        } else {
            // no access
            return;
        }

        $this->submission->downloadFiles(null, $a_only_new, $peer_review_mask_filename);
        // we only get here, if no files have been found for download
        if ($a_only_new) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("exc_all_new_files_offered_already"), true);
        }
        $this->returnToParentObject();
    }

    /**
    * Download newly submitted files of user.
    */
    public function downloadNewReturnedObject(): void
    {
        $this->downloadReturnedObject(true);
    }

    /**
     * User downloads (own) submitted files
     */
    public function downloadObject(): void
    {
        $ilCtrl = $this->ctrl;

        $delivered_id = $this->request->getSubmittedFileId();

        if (!$this->submission->canView()) {
            $this->returnToParentObject();
        }

        if (!is_array($delivered_id) && $delivered_id > 0) {
            $delivered_id = [$delivered_id];
        }
        if (is_array($delivered_id) && $delivered_id !== []) {
            $this->submission->downloadFiles($delivered_id);
            exit;
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("please_select_a_delivered_file_to_download"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }
    }
}
