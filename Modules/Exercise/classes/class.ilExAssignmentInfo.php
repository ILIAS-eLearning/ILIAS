<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class ilExAssignmentInfo
{
    /**
     * @var ilExcAssMemberState
     */
    protected $state;

    /**
     * @var ilExAssignment
     */
    protected $ass;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * Constructor
     */
    public function __construct(int $ass_id, int $user_id)
    {
        global $DIC;

        $this->state = ilExcAssMemberState::getInstanceByIds($ass_id, $user_id);
        $this->lng = $DIC->language();
        $this->ass = new ilExAssignment($ass_id);
        $this->ctrl = $DIC->ctrl();
        $this->user_id = $user_id;
    }

    /**
     * Get instruction info
     *
     * @return array
     */
    public function getInstructionInfo()
    {
        if ($this->state->areInstructionsVisible()) {
            $inst = $this->ass->getInstructionPresentation();
            if (trim($inst)) {
                return [
                    "instruction" => [
                        "txt" => $this->lng->txt("exc_instruction"),
                        "value" => $inst
                    ]
                ];
            }
        }
        return [];
    }

    /**
     * Get instruction file info
     *
     * @param
     * @return
     */
    public function getInstructionFileInfo($readable_ref_id = 0)
    {
        $ctrl = $this->ctrl;
        $ass_files = $this->ass->getFiles();
        if (count($ass_files) > 0) {
            $items = [];

            foreach ($ass_files as $file) {
                $dl_link = "";
                if ($readable_ref_id > 0) {
                    $ctrl->setParameterByClass("ilExSubmissionGUI", "ref_id", $readable_ref_id);
                    $ctrl->setParameterByClass("ilExSubmissionGUI", "ass_id", $this->ass->getId());
                    $ctrl->setParameterByClass("ilExSubmissionGUI", "file", urlencode($file["name"]));
                    $dl_link = $ctrl->getLinkTargetByClass([
                        "ilExerciseHandlerGUI",
                        "ilObjExerciseGUI",
                        "ilExSubmissionGUI"
                    ], "downloadFile");
                    $ctrl->clearParametersByClass("ilExSubmissionGUI");
                }
                $items[] = [
                    "txt" => $file["name"],
                    "value" => $dl_link
                ];
            }
        }
        return $items;
    }


    /**
     *
     *
     * @param
     * @return
     */
    public function getScheduleInfo()
    {
        $lng = $this->lng;
        $ret = [];
        $state = $this->state;

        if ($state->getGeneralStart() > 0) {
            if ($state->getRelativeDeadline()) {
                $txt = $lng->txt("exc_earliest_start_time");
            } else {
                $txt = $lng->txt("exc_start_time");
            }
            $ret["start_time"] = [
                "txt" => $txt,
                "value" => $state->getGeneralStartPresentation()
            ];
        }

        // extended deadline info/warning
        $late_dl = "";
        if ($state->inLateSubmissionPhase()) {
            // extended deadline date should not be presented anywhere
            $late_dl = $state->getOfficialDeadlinePresentation();
            $late_dl = "<br />" . sprintf($lng->txt("exc_late_submission_warning"), $late_dl);
            $late_dl = '<span class="warning">' . $late_dl . '</span>';
        }

        if ($state->getCommonDeadline()) {		// if we have a common deadline (target timestamp)
            $until = $state->getCommonDeadlinePresentation();

            // add late info if no idl
            if ($late_dl &&
                $state->getOfficialDeadline() == $state->getCommonDeadline()) {
                $until .= $late_dl;
            }

            $prop = $lng->txt("exc_edit_until");
            if ($state->exceededOfficialDeadline()) {
                $prop = $lng->txt("exc_ended_on");
            }

            $ret["until"] = ["txt" => $prop, "value" => $until];
        } elseif ($state->getRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
            $ret["time_after_start"] = ["txt" => $lng->txt("exc_rem_time_after_start"), "value" => $state->getRelativeDeadlinePresentation()];
        }

        if ($state->getOfficialDeadline() > $state->getCommonDeadline()) {
            $until = $state->getOfficialDeadlinePresentation();

            // add late info?
            if ($late_dl) {
                $until .= $late_dl;
            }

            $ret["individual_deadline"] = ["txt" => $lng->txt("exc_individual_deadline"), "value" => $until];
        }

        if ($state->hasSubmissionStarted()) {
            $ret["time_to_send"] = ["txt" => $lng->txt("exc_time_to_send"), "value" => "<b>" . $state->getRemainingTimePresentation() . "</b>"];
        }
        return $ret;
    }

    /**
     * Get submission info
     *
     * @return array
     */
    public function getSubmissionInfo()
    {
        // submitted files
        $submission = new ilExSubmission($this->ass, $this->user_id);
        $ret = [];
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();
            if ($submitted["ts"] != "") {
                $ret["submitted"] = [
                    "txt" => $this->lng->txt("exc_last_submission"),
                    "value" => ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME))
                ];
            }
        }
        return $ret;
    }
}
