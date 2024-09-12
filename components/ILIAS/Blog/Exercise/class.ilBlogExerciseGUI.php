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

declare(strict_types=1);

use ILIAS\Blog\StandardGUIRequest;
use ILIAS\Exercise\Submission\SubmissionManager;
use ILIAS\Exercise\Submission\Submission;

/**
 * @ilCtrl_Calls ilBlogExerciseGUI:
 */
class ilBlogExerciseGUI
{
    protected SubmissionManager $submission;
    protected \ILIAS\Blog\Exercise\BlogExercise $blog_exercise;
    protected StandardGUIRequest $blog_request;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected int $node_id;
    protected int $ass_id;
    protected string $file;
    protected \ILIAS\DI\UIServices $ui;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        int $a_node_id,
        \ILIAS\Blog\Exercise\BlogExercise $blog_exercise,
        ilLanguage $lng,
        ilObjUser $user,
        \ILIAS\Blog\InternalGUIService $gui
    ) {
        global $DIC;

        $this->main_tpl = $gui->ui()->mainTemplate();
        $this->ctrl = $gui->ctrl();
        $this->user = $user;
        $this->lng = $lng;
        $this->node_id = $a_node_id;
        $this->blog_request = $gui->standardRequest();

        $this->ass_id = $this->blog_request->getAssId();
        $this->file = $this->blog_request->getAssFile();
        $this->ui = $gui->ui();
        $this->blog_exercise = $blog_exercise;
        $this->submission = $DIC->exercise()->internal()->domain()->submission(
            $this->ass_id
        );
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->ass_id) {
            $this->ctrl->returnToParent($this);
        }

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    protected function downloadExcAssFile(): void
    {
        if ($this->file) {
            $ass = new ilExAssignment($this->ass_id);
            $ass_files = $ass->getFiles();
            if (count($ass_files) > 0) {
                foreach ($ass_files as $file) {
                    if ($file["name"] == $this->file) {
                        ilFileDelivery::deliverFileLegacy($file["fullpath"], $file["name"]);
                    }
                }
            }
        }
    }

    protected function downloadExcSubFile(): void
    {
        $ass = new ilExAssignment($this->ass_id);
        $submissions = $this->submission->getSubmissionsOfUser(
            $this->user->getId()
        );
        /** @var Submission $submitted */
        if ($submitted = $submissions->current()) {
            $user_data = ilObjUser::_lookupName($submitted->getUserId());
            $title = ilObject::_lookupTitle($ass->getExerciseId()) . " - " .
                $ass->getTitle() . " - " .
                $user_data["firstname"] . " " .
                $user_data["lastname"] . " (" .
                $user_data["login"] . ").zip";
            $this->submission->deliverFile(
                $submitted->getUserId(),
                $submitted->getRid(),
                $title
            );
        }
    }

    protected function finalize(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $exc_gui = ilExSubmissionObjectGUI::initGUIForSubmit($this->ass_id);
        $exc_gui->submitBlog($this->node_id);

        $this->main_tpl->setOnScreenMessage('success', $lng->txt("blog_finalized"), true);
        $ilCtrl->returnToParent($this);
    }

    /**
     * Get submit link
     * @throws ilCtrlException
     */
    public function getSubmitButton(
        int $ass_id
    ): ?\ILIAS\UI\Component\Button\Primary {
        $ilCtrl = $this->ctrl;
        $ui = $this->ui;
        $lng = $this->lng;

        $state = ilExcAssMemberState::getInstanceByIds($ass_id, $this->user->getId());

        if ($state->isSubmissionAllowed()) {
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $ass_id);
            $submit_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "finalize");
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");
            return $ui->factory()->button()->primary($lng->txt("blog_finalize_blog"), $submit_link);
        }
        return null;
    }

    /**
     * @throws ilCtrlException
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function getDownloadSubmissionButton(
        int $ass_id
    ): ?\ILIAS\UI\Component\Button\Standard {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        // submitted files
        $submission = new ilExSubmission(new ilExAssignment($ass_id), $this->user->getId());
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();
            if ($submitted?->getTimestamp() !== "") {
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $ass_id);
                $dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcSubFile");
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");
                return $ui->factory()->button()->standard($lng->txt("blog_download_submission"), $dl_link);
            }
        }
        return null;
    }


    public function getActionButtons(): array
    {
        $be = $this->blog_exercise;

        $buttons = [];
        foreach ($be->getAssignmentsOfBlog() as $exercise) {
            $ass_id = $exercise["ass_id"];
            $buttons[$ass_id] = [];
            $submit_button = $this->getSubmitButton($ass_id);
            if ($submit_button) {
                $buttons[$ass_id][] = $submit_button;
            }
            $download_button = $this->getDownloadSubmissionButton($ass_id);
            if ($download_button) {
                $buttons[$ass_id][] = $download_button;
            }
        }

        return $buttons;
    }
}
