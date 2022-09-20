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

use ILIAS\Portfolio\StandardGUIRequest;

/**
 * Class ilPortfolioExerciseGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilPortfolioExerciseGUI:
 */
class ilPortfolioExerciseGUI
{
    protected StandardGUIRequest $port_request;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected int $user_id;
    protected int $obj_id;
    protected int $ass_id;
    protected string $file;
    protected ilPortfolioExercise $pe;
    protected \ILIAS\DI\UIServices $ui;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        int $a_user_id,
        int $a_obj_id
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user_id = $a_user_id;
        $this->obj_id = $a_obj_id;

        $this->port_request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->ass_id = $this->port_request->getExcAssId();
        $this->file = $this->port_request->getExcFile();
        $this->ui = $DIC->ui();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->ass_id ||
            !$this->user_id) {
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

    /**
     * @todo get rid of mixed return type
     * @return array|string|void
     */
    public static function checkExercise(
        int $a_user_id,
        int $a_obj_id,
        bool $a_add_submit = false,
        bool $as_array = false
    ) {
        $pe = new ilPortfolioExercise($a_user_id, $a_obj_id);

        $info = [];
        foreach ($pe->getAssignmentsOfPortfolio() as $exercise) {
            $part = self::getExerciseInfo($a_user_id, $exercise["ass_id"], $a_add_submit, $as_array);
            if ($part) {
                $info[] = $part;
            }
        }
        if (count($info) && !$as_array) {
            return implode("<br />", $info);
        }

        if ($as_array) {
            return $info;
        }
    }

    /**
     * @deprecated
     * @return string|array
     * @throws ilCtrlException
     * @throws ilDateTimeException
     * @throws ilExcUnknownAssignmentTypeException
     */
    protected static function getExerciseInfo(
        int $a_user_id,
        int $a_assignment_id,
        bool $a_add_submit = false,
        bool $as_array = false
    ) {
        global $DIC;

        $ui = $DIC->ui();

        $links = [];
        $buttons = [];
        $elements = [];

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $rel = false;

        $ass = new ilExAssignment($a_assignment_id);
        $exercise_id = $ass->getExerciseId();
        if (!$exercise_id) {
            return "";
        }

        // is the assignment still open?
        $times_up = $ass->afterDeadlineStrict();

        // exercise goto
        $ref_ids = ilObject::_getAllReferences($exercise_id);
        $exc_ref_id = array_shift($ref_ids);
        $exc_link = ilLink::_getStaticLink($exc_ref_id, "exc");

        $info_arr["ass_title"] = $ass->getTitle();
        $text = sprintf(
            $lng->txt("prtf_exercise_info"),
            $ass->getTitle(),
            ilObject::_lookupTitle($exercise_id)
        );
        $links[] = $ui->factory()->link()->standard(ilObject::_lookupTitle($exercise_id), $exc_link);
        $info_arr["exc_title"] = ilObject::_lookupTitle($exercise_id);

        // submit button
        if ($a_add_submit && !$times_up && !$as_array) {
            $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $a_assignment_id);
            $submit_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "finalize");
            $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");

            $buttons[] = $ui->factory()->button()->primary($lng->txt("prtf_finalize_portfolio"), $submit_link);
        }

        // submitted files
        $submission = new ilExSubmission($ass, $a_user_id);
        $info_arr["submitted"] = false;
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();

            if (!$as_array) {
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $a_assignment_id);
                $dl_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "downloadExcSubFile");
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");

                $rel = ilDatePresentation::useRelativeDates();
                ilDatePresentation::setUseRelativeDates(false);

                $text .= "<p>" . sprintf(
                    $lng->txt("prtf_exercise_submitted_info"),
                    ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME)),
                    ""
                ) . "</p>";
                $buttons[] = $ui->factory()->button()->standard($lng->txt("prtf_download_submission"), $dl_link);
            }

            ilDatePresentation::setUseRelativeDates($rel);
            $info_arr["submitted_date"] = ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME));
            $info_arr["submitted"] = true;
            if ($submitted["ts"] == "") {
                $info_arr["submitted"] = false;
            }
        }

        // work instructions incl. files

        $tooltip = "";

        $inst = $ass->getInstruction();
        if ($inst) {
            $tooltip .= nl2br($inst);
        }

        $ass_files = $ass->getFiles();
        if (!$as_array && count($ass_files) > 0) {
            if ($tooltip) {
                $tooltip .= "<br /><br />";
            }

            $items = [];

            foreach ($ass_files as $file) {
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $a_assignment_id);
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "file", urlencode($file["name"]));
                $dl_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "downloadExcAssFile");
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "file", "");
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");

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

        if ($as_array) {
            return $info_arr;
        }

        $elements[] = $ui->factory()->messageBox()->info($text)
            ->withLinks($links)
            ->withButtons($buttons);

        return $ui->renderer()->render($elements);
    }

    public function downloadExcAssFile(): void
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

    public function downloadExcSubFile(): void
    {
        $ass = new ilExAssignment($this->ass_id);
        $submission = new ilExSubmission($ass, $this->user_id);
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

    /**
     * Finalize and submit portfolio to exercise
     */
    protected function finalize(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $exc_gui = ilExSubmissionObjectGUI::initGUIForSubmit($this->ass_id);
        $exc_gui->submitPortfolio($this->obj_id);

        $this->main_tpl->setOnScreenMessage('success', $lng->txt("prtf_finalized"), true);
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

        $state = ilExcAssMemberState::getInstanceByIds($ass_id, $this->user_id);

        if ($state->isSubmissionAllowed()) {
            $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $ass_id);
            $submit_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "finalize");
            $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");
            $button = $ui->factory()->button()->primary($lng->txt("prtf_finalize_portfolio"), $submit_link);
            return $button;
        }
        return null;
    }

    public function getDownloadSubmissionButton(
        int $ass_id
    ): ?\ILIAS\UI\Component\Button\Standard {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        // submitted files
        $submission = new ilExSubmission(new ilExAssignment($ass_id), $this->user_id);
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();
            if ($submitted["ts"] != "") {
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $ass_id);
                $dl_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "downloadExcSubFile");
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");
                $button = $ui->factory()->button()->standard($lng->txt("prtf_download_submission"), $dl_link);
                return $button;
            }
        }
        return null;
    }


    /**
     * Get action buttons
     */
    public function getActionButtons(): array
    {
        $pe = new ilPortfolioExercise($this->user_id, $this->obj_id);

        $buttons = [];
        foreach ($pe->getAssignmentsOfPortfolio() as $exercise) {
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
