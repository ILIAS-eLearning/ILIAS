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

use ILIAS\Blog\StandardGUIRequest;

/**
 * Class ilBlogExerciseGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilBlogExerciseGUI:
 */
class ilBlogExerciseGUI
{
    protected StandardGUIRequest $blog_request;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected int $node_id;
    protected int $ass_id;
    protected string $file;
    protected \ILIAS\DI\UIServices $ui;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(int $a_node_id)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->node_id = $a_node_id;
        $this->blog_request = $DIC->blog()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->ass_id = $this->blog_request->getAssId();
        $this->file = $this->blog_request->getAssFile();
        $this->ui = $DIC->ui();
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

    public static function checkExercise(
        int $a_node_id
    ): string {
        $be = new ilBlogExercise($a_node_id);

        $info = [];

        foreach ($be->getAssignmentsOfBlog() as $ass) {
            $part = self::getExerciseInfo($ass["ass_id"]);
            if ($part) {
                $info[] = $part;
            }
        }
        if (count($info) > 0) {
            return implode("<br />", $info);
        }
        return "";
    }

    protected static function getExerciseInfo(
        int $a_assignment_id
    ): string {
        global $DIC;

        $ui = $DIC->ui();

        $links = [];
        $buttons = [];
        $elements = [];
        $items = [];

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();

        $ass = new ilExAssignment($a_assignment_id);
        $exercise_id = $ass->getExerciseId();
        if (!$exercise_id) {
            return "";
        }

        // is the assignment still open?
        $times_up = $ass->afterDeadlineStrict();

        // exercise goto
        $exc_ref_id = current(ilObject::_getAllReferences($exercise_id));
        $exc_link = ilLink::_getStaticLink($exc_ref_id, "exc");

        $text = sprintf(
            $lng->txt("blog_exercise_info"),
            $ass->getTitle(),
            ilObject::_lookupTitle($exercise_id)
        );
        $links[] = $ui->factory()->link()->standard(ilObject::_lookupTitle($exercise_id), $exc_link);

        // submit button
        if (!$times_up) {
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
            $submit_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "finalize");
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");

            $buttons[] = $ui->factory()->button()->primary($lng->txt("blog_finalize_blog"), $submit_link);
        }

        // submitted files
        $submission = new ilExSubmission($ass, $ilUser->getId());
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();

            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
            $dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcSubFile");
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");

            $rel = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);

            $text .= "<br />" . sprintf(
                $lng->txt("blog_exercise_submitted_info"),
                ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME)),
                ""
            );

            ilDatePresentation::setUseRelativeDates($rel);
            $buttons[] = $ui->factory()->button()->standard($lng->txt("blog_download_submission"), $dl_link);
        }


        // work instructions incl. files

        $tooltip = "";

        $inst = $ass->getInstruction();
        if ($inst) {
            $tooltip .= nl2br($inst);
        }

        $ass_files = $ass->getFiles();
        if (count($ass_files) > 0) {
            $tooltip .= "<br /><br />";

            foreach ($ass_files as $file) {
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
                $ilCtrl->setParameterByClass("ilblogexercisegui", "file", urlencode($file["name"]));
                $dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcAssFile");
                $ilCtrl->setParameterByClass("ilblogexercisegui", "file", "");
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");

                $items[] = $ui->renderer()->render($ui->factory()->button()->shy($file["name"], $dl_link));
            }
            $list = $ui->factory()->listing()->unordered($items);
            $tooltip .= $ui->renderer()->render($list);
        }

        if ($tooltip) {
            $modal = $ui->factory()->modal()->roundtrip($lng->txt("exc_instruction"), $ui->factory()->legacy($tooltip))
                ->withCancelButtonLabel("close");
            $elements[] = $modal;
            $buttons[] = $ui->factory()->button()->standard($lng->txt("exc_instruction"), '#')
                ->withOnClick($modal->getShowSignal());
        }

        $elements[] = $ui->factory()->messageBox()->info($text)
            ->withLinks($links)
            ->withButtons($buttons);

        return $ui->renderer()->render($elements);
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
        $ilUser = $this->user;

        $ass = new ilExAssignment($this->ass_id);
        $submission = new ilExSubmission($ass, $ilUser->getId());
        $submitted = $submission->getFiles();
        if (count($submitted) > 0) {
            $submitted = array_pop($submitted);

            $user_data = ilObjUser::_lookupName($submitted["user_id"]);
            $title = ilObject::_lookupTitle($submitted["obj_id"]) . " - " .
                $ass->getTitle() . " - " .
                $user_data["firstname"] . " " .
                $user_data["lastname"] . " (" .
                $user_data["login"] . ").zip";

            ilFileDelivery::deliverFileLegacy($submitted["filename"], $title);
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
            if ($submitted["ts"] != "") {
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
        $be = new ilBlogExercise($this->node_id);

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
