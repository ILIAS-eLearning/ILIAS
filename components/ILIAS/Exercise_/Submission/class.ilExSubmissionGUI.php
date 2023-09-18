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

use ILIAS\Exercise\GUIRequest;

/**
 * Class ilExSubmissionGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilExSubmissionGUI: ilExSubmissionTeamGUI, ilExSubmissionFileGUI
 * @ilCtrl_Calls ilExSubmissionGUI: ilExSubmissionTextGUI, ilExSubmissionObjectGUI
 * @ilCtrl_Calls ilExSubmissionGUI: ilExPeerReviewGUI
 */
class ilExSubmissionGUI
{
    public const MODE_OVERVIEW_CONTENT = 1;

    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected ilObjExercise $exercise;
    protected ilExSubmission $submission;
    protected ilExAssignment $assignment;
    protected ilExAssignmentTypesGUI $type_guis;
    protected ?int $user_id;
    protected GUIRequest $request;

    public function __construct(
        ilObjExercise $a_exercise,
        ilExAssignment $a_ass,
        int $a_user_id = null
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilUser = $DIC->user();

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }

        $this->assignment = $a_ass;
        $this->exercise = $a_exercise;
        $this->user_id = $a_user_id;

        $this->type_guis = ilExAssignmentTypesGUI::getInstance();

        // #12337
        if (!$this->exercise->members_obj->isAssigned($a_user_id)) {
            $this->exercise->members_obj->assignMember($a_user_id);
        }

        // public submissions ???
        $public_submissions = false;
        if ($this->exercise->getShowSubmissions() &&
            $this->exercise->getTimestamp() - time() <= 0) { // ???
            $public_submissions = true;
        }
        $this->submission = new ilExSubmission($a_ass, $a_user_id, null, false, $public_submissions);

        // :TODO:
        $this->ctrl = $ilCtrl;
        $this->tabs_gui = $ilTabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->request = $DIC->exercise()->internal()->gui()->request();
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("listPublicSubmissions");

        switch ($class) {
            case "ilexsubmissionteamgui":
                // team gui has no base gui - see we have to handle tabs here

                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "returnToParent")
                );

                // forward to type gui
                if ($this->submission->getSubmissionType() != ilExSubmission::TYPE_REPO_OBJECT) {
                    $this->tabs_gui->addTab(
                        "submission",
                        $this->lng->txt("exc_submission"),
                        $this->ctrl->getLinkTargetByClass("ilexsubmission" . $this->submission->getSubmissionType() . "gui", "")
                    );
                }

                $gui = new ilExSubmissionTeamGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;

            case "ilexsubmissiontextgui":
                $gui = new ilExSubmissionTextGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;

            case "ilexsubmissionfilegui":
                $gui = new ilExSubmissionFileGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;

            case "ilexsubmissionobjectgui":
                $gui = new ilExSubmissionObjectGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;

            case "ilexpeerreviewgui":
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "returnToParent")
                );

                $peer_gui = new ilExPeerReviewGUI($this->assignment, $this->submission);
                $this->ctrl->forwardCommand($peer_gui);
                break;

            default:


                // forward to type gui
                if ($this->type_guis->isExAssTypeGUIClass($class)) {
                    $type_gui = $this->type_guis->getByClassName($class);
                    $type_gui->setSubmission($this->submission);
                    $type_gui->setExercise($this->exercise);
                    $ilCtrl->forwardCommand($type_gui);
                }

                $this->{$cmd . "Object"}();
                break;
        }
    }

    public static function getOverviewContent(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission,
        ilObjExercise $a_exc
    ): void {
        global $DIC;

        $ilCtrl = $DIC->ctrl();

        if (!$a_submission->canView()) {
            return;
        }

        $ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", $a_submission->getAssignment()->getId());

        if ($a_submission->getAssignment()->hasTeam()) {
            ilExSubmissionTeamGUI::getOverviewContent($a_info, $a_submission);
        }

        $submission_type = $a_submission->getSubmissionType();
        // old handling -> forward to submission type gui class
        // @todo migrate everything to new concept
        if ($submission_type != ilExSubmission::TYPE_REPO_OBJECT) {
            $class = "ilExSubmission" . $submission_type . "GUI";
            /** @var ilExSubmissionFileGUI|ilExSubmissionTextGUI|ilExSubmissionTeamGUI $class */
            $class::getOverviewContent($a_info, $a_submission);
        } else { // new: get HTML from assignemt type gui class
            $sub_gui = new ilExSubmissionGUI($a_exc, $a_submission->getAssignment());
            $ilCtrl->getHTML($sub_gui, array(
                "mode" => self::MODE_OVERVIEW_CONTENT,
                "info" => $a_info,
                "submission" => $a_submission
            ));
        }

        $ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", "");
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function getHTML(array $par): string
    {
        switch ($par["mode"]) {
            // get overview content from ass type gui
            case self::MODE_OVERVIEW_CONTENT:
                $type_gui = $this->type_guis->getById($par["submission"]->getAssignment()->getType());
                $type_gui->getOverviewContent($par["info"], $par["submission"]);
                break;
        }
        return "";
    }

    public function listPublicSubmissionsObject(): void
    {
        $ilTabs = $this->tabs_gui;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->exercise->getShowSubmissions()) {
            $this->returnToParentObject();
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "returnToParent")
        );

        if ($this->assignment->getType() != ilExAssignment::TYPE_TEXT) {
            $tab = new ilPublicSubmissionsTableGUI($this, "listPublicSubmissions", $this->assignment);
            $this->tpl->setContent($tab->getHTML());
        } else {
            // #13271
            $tbl = new ilExAssignmentListTextTableGUI($this, "listPublicSubmissions", $this->assignment, false, true);
            $this->tpl->setContent($tbl->getHTML());
        }
    }

    /**
     * Download feedback file
     */
    public function downloadFeedbackFileObject(): bool
    {
        $file = $this->request->getFile();

        if (!isset($file)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_select_one_file"), true);
            $this->ctrl->redirect($this, "view");
        }

        // check, whether file belongs to assignment
        $storage = new ilFSStorageExercise($this->exercise->getId(), $this->assignment->getId());
        $files = $storage->getFeedbackFiles($this->submission->getFeedbackId());
        $file_exist = false;
        foreach ($files as $fb_file) {
            if ($fb_file == $file) {
                $file_exist = true;
                break;
            }
        }

        if (!$file_exist) {
            return false;
        }

        // check whether assignment has already started
        if (!$this->assignment->notStartedYet()) {
            // deliver file
            $p = $storage->getFeedbackFilePath($this->submission->getFeedbackId(), $file);
            ilFileDelivery::deliverFileLegacy($p, $file);
        }

        return true;
    }

    public function downloadGlobalFeedbackFileObject(): void
    {
        $ilCtrl = $this->ctrl;

        $state = ilExcAssMemberState::getInstanceByIds($this->assignment->getId(), $this->user_id);

        // fix bug 28466, this code should be streamlined with the if above and
        // the presentation of the download link in the ilExAssignmentGUI->addSubmission
        if (!$state->isGlobalFeedbackFileAccessible($this->submission)) {
            $ilCtrl->redirect($this, "returnToParent");
        }

        // this is due to temporary bug in handleGlobalFeedbackFileUpload that missed the last "/"
        $file = (is_file($this->assignment->getGlobalFeedbackFilePath()))
            ? $this->assignment->getGlobalFeedbackFilePath()
            : $this->assignment->getGlobalFeedbackFileStoragePath() . $this->assignment->getFeedbackFile();

        ilFileDelivery::deliverFileLegacy($file, $this->assignment->getFeedbackFile());
    }

    public function downloadFileObject(): bool
    {
        $file = $this->request->getFile();

        if (!isset($file)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_select_one_file"), true);
            $this->ctrl->redirect($this, "view");
        }

        // check whether assignment as already started
        $state = ilExcAssMemberState::getInstanceByIds($this->assignment->getId(), $this->user_id);
        if ($state->areInstructionsVisible()) {
            // check, whether file belongs to assignment
            $files = $this->assignment->getFiles();
            $file_exist = false;
            foreach ($files as $lfile) {
                if ($lfile["name"] == $file) {
                    // deliver file
                    ilFileDelivery::deliverFileLegacy($lfile["fullpath"], $file);
                    exit();
                }
            }
            if (!$file_exist) {
                return false;
            }
        }

        return true;
    }

    public function returnToParentObject(): void
    {
        $this->ctrl->returnToParent($this);
    }
}
