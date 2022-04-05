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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentInfo
{
    protected ilExcAssMemberState $state;
    protected ilExAssignment $ass;
    protected int $user_id;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    /**
     * @throws ilExcUnknownAssignmentTypeException
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

    public function getInstructionInfo() : array
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

    public function getInstructionFileInfo(int $readable_ref_id = 0) : array
    {
        $ctrl = $this->ctrl;
        $ass_files = $this->ass->getFiles();
        $items = [];
        if (count($ass_files) > 0) {
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
     * @throws ilDateTimeException
     */
    public function getScheduleInfo() : array
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
     * @throws ilDateTimeException
     */
    public function getSubmissionInfo() : array
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
