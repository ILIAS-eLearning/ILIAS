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
 * Handles everything about the state (current phase) of a user in an assignment using
 * assignment, individual deadline, user and team information.
 *
 * - General Start: As entered in settings. For absolute deadlines, this also starts the submission, for relative
 *   deadline this allows the user to start the submission period. (0 = immediately)
 * - Individual Start: TS when user hits "Start" button for an assignment using a relative deadline
 * - Submission Start: For absolute deadlines this is General Start, for relative deadlines Individual Start
 * - Deadline: absolute Deadline (e.g. 5.12.2017) as set in settings
 * - Relative Deadline: relative Deadline (e.g. 10 Days) as set in settings
 * - Last Submission for Relative Deadlines: As set in the settings
 * - Calculated Deadline: Min of (Starting Timestamp + Relative Deadline, Last Submission for Relative Deadlines)
 * - Individual Deadline: Set by tutor in "Submissions and Grade" screen
 * - Common Deadline: Deadline or Calculated Deadline
 *   Used for "Ended on" or "Edit Until" presentation
 * - Official Deadline: Max of (Deadline and Individual Deadline) or (Calculated Deadline and Individual Deadline)
 * - Effective Deadline: Max of official deadline and grace period end date
 * - Grace Period End Date: As being set in the settings of assignmet by tutor
 * - Grace Period: Period between Official Deadline and Grace Period End Date.
 * - Submission Period: From Submission Start (if not given immediately) to Max of (Official Deadline and Grace Period End Date)
 * - Late Submission Period: Submissions being handed in during Grace Period
 * - Peer Review Start: Max of (Official Deadline OF ALL USERS and Grace Period End Date)
 * - Peer Review Deadline: As being set in the settings of assignmet by tutor
 * - Peer Review Period: From Peer Feedback Start to Peer Feedback Deadline (may be infinite, if no deadline given)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcAssMemberState
{
    protected int $ass_id;
    protected int $user_id;
    protected ilExAssignment $assignment;
    protected int $time;
    protected ilLanguage $lng;

    /**
     * either user id or team id, if this is a team assignment
     * and the user is member of a team, in this case is_team is true
     */
    protected ?int $member_id;
    protected ?int $team_id = 0;
    protected bool $is_team = false;
    protected ilExcIndividualDeadline $idl;

    protected function __construct(
        ilExAssignment $a_ass,
        ilObjUser $a_user,
        ilExcIndividualDeadline $a_idl,
        int $a_time,
        ilLanguage $lng,
        ilExAssignmentTeam $a_team = null
    ) {
        $this->time = $a_time;
        $this->ass_id = $a_ass->getId();
        $this->user_id = $a_user->getId();
        $this->member_id = $a_user->getId();
        $this->lng = $lng;

        $this->assignment = $a_ass;

        // check team status
        $this->is_team = false;
        if ($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM) {
            if ($a_team->getId()) {
                $this->member_id = $a_team->getId();
                $this->team_id = $a_team->getId();
                $this->is_team = true;
            }
        }

        $this->idl = $a_idl;
    }

    // Get instance by IDs (recommended for consumer code)
    public static function getInstanceByIds(
        int $a_ass_id,
        int $a_user_id = 0
    ) : ilExcAssMemberState {
        global $DIC;

        $lng = $DIC->language();
        $user = ($a_user_id > 0)
            ? new ilObjUser($a_user_id)
            : $DIC->user();

        $ass = new ilExAssignment($a_ass_id);

        $member_id = $user->getId();
        $is_team = false;
        $team = null;
        if ($ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM) {		// better move this to ilExcIndividualDeadline
            $team = ilExAssignmentTeam::getInstanceByUserId($a_ass_id, $user->getId());
            if ($team->getId()) {
                $member_id = $team->getId();
                $is_team = true;
            }
        }

        // note: team may be not null, but is_team still false
        $idl = ilExcIndividualDeadline::getInstance($a_ass_id, $member_id, $is_team);

        return self::getInstance($ass, $user, $idl, time(), $lng, $team);
    }

    /**
     * Usually you should prefer to use getInstanceByIds. If you use getInstance you need to ensure consistency (e.g. deadline needs to match user)
     */
    public static function getInstance(
        ilExAssignment $a_ass,
        ilObjUser $a_user,
        ilExcIndividualDeadline $a_idl,
        int $a_time,
        ilLanguage $lng,
        ilExAssignmentTeam $a_team = null
    ) : ilExcAssMemberState {
        return new self($a_ass, $a_user, $a_idl, $a_time, $lng, $a_team);
    }

    public function getIndividualDeadlineObject() : ilExcIndividualDeadline
    {
        return $this->idl;
    }

    public function getGeneralStart() : ?int
    {
        return $this->assignment->getStartTime();
    }

    /**
     * @return string
     * @throws ilDateTimeException
     */
    public function getGeneralStartPresentation() : string
    {
        if ($this->getGeneralStart()) {
            return $this->getTimePresentation($this->getGeneralStart());
        }
        return "";
    }

    public function getIndividualStart() : int
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            return $this->idl->getStartingTimestamp();
        }
        return 0;
    }

    public function hasGenerallyStarted() : bool
    {
        return !$this->assignment->notStartedYet();
    }

    /**
     * Calculated deadline is only given, if a relative deadline is given
     * and the user started the assignment
     * the value may be restricted by the last submission date for relative deadlines
     */
    public function getCalculatedDeadline() : int
    {
        $calculated_deadline = 0;
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            if ($this->idl->getStartingTimestamp() && $this->assignment->getRelativeDeadline()) {
                $calculated_deadline = $this->idl->getStartingTimestamp() + ($this->assignment->getRelativeDeadline() * 24 * 60 * 60);
            }
            if ($this->assignment->getRelDeadlineLastSubmission() > 0 &&
                $calculated_deadline > $this->assignment->getRelDeadlineLastSubmission()) {
                $calculated_deadline = $this->assignment->getRelDeadlineLastSubmission();
            }
        }
        return $calculated_deadline;
    }

    public function getRelativeDeadline() : int
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            return $this->assignment->getRelativeDeadline();
        }
        return 0;
    }

    public function getLastSubmissionOfRelativeDeadline() : int
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            return $this->assignment->getRelDeadlineLastSubmission();
        }
        return 0;
    }

    public function getRelativeDeadlinePresentation() : string
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            return $this->getRelativeDeadline() . " " . $this->lng->txt("days");
        }
        return "";
    }

    /**
     * Get official deadline (individual deadline, fixed deadline or
     * calculated deadline (using relative deadline and starting ts))
     * Grace period is not taken into account here.
     */
    public function getOfficialDeadline() : int
    {
        $dl = $this->idl->getIndividualDeadline();		// team or user individual deadline

        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_ABSOLUTE) {	// absolute deadline
            return max($this->assignment->getDeadline(), $dl);				// take what's greater: idl or abs deadline
        }

        // relative deadline: take max idl or calculated deadline
        return max($this->getCalculatedDeadline(), $dl);
    }

    /**
     * @return string
     * @throws ilDateTimeException
     */
    public function getOfficialDeadlinePresentation() : string
    {
        if ($this->getOfficialDeadline() > 0) {
            return $this->getTimePresentation($this->getOfficialDeadline());
        }

        return "";
    }

    /**
     * @return string
     * @throws ilDateTimeException
     */
    public function getLastSubmissionOfRelativeDeadlinePresentation() : string
    {
        if ($this->getLastSubmissionOfRelativeDeadline() > 0) {
            return $this->getTimePresentation($this->getLastSubmissionOfRelativeDeadline());
        }

        return "";
    }

    // Check if official deadline exists and has ended
    public function exceededOfficialDeadline() : bool
    {
        $od = $this->getOfficialDeadline();
        if ($od && $od < time()) {
            return true;
        }
        return false;
    }

    /**
     * Remaining time presentation (based on official deadline)
     * @return string
     * @throws ilDateTimeException
     */
    public function getRemainingTimePresentation() : string
    {
        $lng = $this->lng;
        $official_deadline = $this->getOfficialDeadline();
        if ($official_deadline == 0) {
            return $lng->txt("exc_no_deadline_specified");
        }
        if ($official_deadline - $this->time <= 0) {
            $time_str = $lng->txt("exc_time_over_short");
        } else {
            $time_str = ilLegacyFormElementsUtil::period2String(new ilDateTime($official_deadline, IL_CAL_UNIX));
        }

        return $time_str;
    }

    public function getIndividualDeadline() : int
    {
        if ($this->idl->getIndividualDeadline() > $this->getCommonDeadline()) {
            return $this->idl->getIndividualDeadline();
        }
        return 0;
    }

    /**
     * @return string
     * @throws ilDateTimeException
     */
    public function getIndividualDeadlinePresentation() : string
    {
        if ($this->getIndividualDeadline() > 0) {
            return $this->getTimePresentation($this->getIndividualDeadline());
        }

        return "";
    }

    // Get common deadline (no individual deadline or grace period included)
    public function getCommonDeadline() : int
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_ABSOLUTE) {	// absolute deadline
            return $this->assignment->getDeadline();
        }

        return $this->getCalculatedDeadline();
    }

    /**
     * @return string
     * @throws ilDateTimeException
     */
    public function getCommonDeadlinePresentation() : string
    {
        if ($this->getCommonDeadline() > 0) {
            return $this->getTimePresentation($this->getCommonDeadline());
        }

        return "no deadline";
    }

    // Get effective deadline (max of official deadline and grace end period) for the user
    public function getEffectiveDeadline() : int
    {
        return max($this->getOfficialDeadline(), $this->assignment->getExtendedDeadline());
    }

    public function getPeerReviewDeadline() : int
    {
        if ($this->assignment->getPeerReview() &&
            $this->assignment->getPeerReviewDeadline()) {
            return $this->assignment->getPeerReviewDeadline();
        }
        return 0;
    }

    /**
     * @return string
     * @throws ilDateTimeException
     */
    public function getPeerReviewDeadlinePresentation() : string
    {
        if ($this->getPeerReviewDeadline() > 0) {
            return $this->getTimePresentation($this->getPeerReviewDeadline());
        }

        return "no peer review deadline";
    }

    // Is peer reviewing currently allowed
    public function isPeerReviewAllowed() : bool
    {
        if ($this->assignment->getPeerReview() && $this->hasSubmissionEndedForAllUsers()
            && ($this->getPeerReviewDeadline() == 0 || $this->getPeerReviewDeadline() > $this->time)) {
            return true;
        }

        return false;
    }

    /**
     * @param $a_timestamp
     * @return string
     * @throws ilDateTimeException
     */
    protected function getTimePresentation($a_timestamp) : string
    {
        if ($a_timestamp > 0) {
            return ilDatePresentation::formatDate(new ilDateTime($a_timestamp, IL_CAL_UNIX));
        }

        return "";
    }

    public function areInstructionsVisible() : bool
    {
        return $this->hasSubmissionStarted();
    }

    public function inLateSubmissionPhase() : bool
    {
        // official deadline is done, but submission still allowed
        if ($this->getOfficialDeadline() &&
            $this->getOfficialDeadline() < $this->time &&
            $this->isSubmissionAllowed()) {
            return true;
        }
        return false;
    }
    

    /**
     * Check if the submission phase has started for the current user
     * (if the assignment is generally started and for relative deadlines,
     * if the user started the assignment)
     */
    public function hasSubmissionStarted() : bool
    {
        if ($this->hasGenerallyStarted() && ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_ABSOLUTE ||
                $this->getIndividualStart() > 0)) {
            return true;
        }
        return false;
    }

    // Check if the submission phase has ended for the current user
    public function hasSubmissionEnded() : bool
    {
        if ($this->getEffectiveDeadline() == 0) {
            return false;
        }

        if ($this->time > $this->getEffectiveDeadline()) {
            return true;
        }
        return false;
    }

    // Has submission ended for all users
    public function hasSubmissionEndedForAllUsers() : bool
    {
        $global_subm_end = max($this->getEffectiveDeadline(), $this->assignment->getLastPersonalDeadline());

        if ($global_subm_end == 0) {
            return false;
        }

        if ($this->time > $global_subm_end) {
            return true;
        }
        return false;
    }

    public function isSubmissionAllowed() : bool
    {
        if ($this->hasSubmissionStarted() && !$this->hasSubmissionEnded()) {
            return true;
        }
        return false;
    }

    // Is global feedback file accessible?
    public function isGlobalFeedbackFileAccessible(ilExSubmission $submission) : bool
    {
        if (!$this->assignment->getFeedbackFile()) {
            return false;
        }

        // global feedback / sample solution
        if ($this->assignment->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE) {
            $access = $this->hasSubmissionEndedForAllUsers();
        } elseif ($this->assignment->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_CUSTOM) {
            $access = $this->assignment->afterCustomDate();
        } else {
            $access = $submission->hasSubmitted();
        }

        return $access;
    }
}
