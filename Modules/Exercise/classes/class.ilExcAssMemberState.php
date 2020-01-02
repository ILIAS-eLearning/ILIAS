<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

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
 * - Calculated Deadline: Starting Timestamp + Relative Deadline
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
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ModulesExercise
 */
class ilExcAssMemberState
{
    /**
     * @var int
     */
    protected $ass_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var ilExAssignment
     */
    protected $assignment;

    /**
     * @var int either user id or team id, if this is a team assignment and the user is member of a team, in this case is_team is true
     */
    protected $member_id;

    /**
     * @var int
     */
    protected $team_id = 0;

    /**
     * @var bool
     */
    protected $is_team = false;

    /**
     * ilExcAssMemberState constructor.
     * @param int $a_ass_id assignment id
     * @param int $a_user_id user id
     */
    protected function __construct(ilExAssignment $a_ass, ilObjUser $a_user, ilExcIndividualDeadline $a_idl, $a_time, ilLanguage $lng, ilExAssignmentTeam $a_team = null)
    {
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

    /**
     * Get instance by IDs (recommended for consumer code)
     *
     * @param int $a_ass_id assignment id
     * @param int $a_user_id user id
     * @return ilExcAssMemberState
     */
    public static function getInstanceByIds($a_ass_id, $a_user_id = 0)
    {
        global $DIC;

        $lng = $DIC->language();
        $user = ($a_user_id > 0)
            ? new ilObjUser($a_user_id)
            : $DIC->user();

        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        $ass = new ilExAssignment($a_ass_id);

        $member_id = $user->getId();
        $is_team = false;
        $team = null;
        if ($ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM) {		// better move this to ilExcIndividualDeadline
            include_once("./Modules/Exercise/classes/class.ilExAssignmentTeam.php");
            $team = ilExAssignmentTeam::getInstanceByUserId($a_ass_id, $user->getId());
            if ($team->getId()) {
                $member_id = $team->getId();
                $is_team = true;
            }
        }

        // note: team may be not null, but is_team still false
        include_once("./Modules/Exercise/classes/class.ilExcIndividualDeadline.php");
        $idl = ilExcIndividualDeadline::getInstance($a_ass_id, $member_id, $is_team);

        return self::getInstance($ass, $user, $idl, time(), $lng, $team);
    }

    /**
     * Get instance by dependencies.
     *
     * Usually you should prefer to use getInstanceByIds. If you use getInstance you need to ensure consistency (e.g. deadline needs to match user)
     */
    public static function getInstance(ilExAssignment $a_ass, ilObjUser $a_user, ilExcIndividualDeadline $a_idl, $a_time, ilLanguage $lng, ilExAssignmentTeam $a_team = null)
    {
        return new self($a_ass, $a_user, $a_idl, $a_time, $lng, $a_team);
    }

    /**
     * Get individual deadline object
     *
     * @return ilExcIndividualDeadline
     */
    public function getIndividualDeadlineObject()
    {
        return $this->idl;
    }
    
    
    /**
     * Get general start
     *
     * @param
     * @return
     */
    public function getGeneralStart()
    {
        return $this->assignment->getStartTime();
    }

    /**
     * Get start presentation
     *
     * @return string
     */
    public function getGeneralStartPresentation()
    {
        if ($this->getGeneralStart()) {
            return $this->getTimePresentation($this->getGeneralStart());
        }
        return "";
    }

    /**
     * Get individual start
     *
     * @return int
     */
    public function getIndividualStart()
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            return $this->idl->getStartingTimestamp();
        }
        return 0;
    }


    /**
     * Has started
     *
     * @return bool
     */
    public function hasGenerallyStarted()
    {
        return !$this->assignment->notStartedYet();
    }

    /**
     * Calculated deadline is only given, if a relative deadline is given and the user started the assignment
     *
     * @return int
     */
    public function getCalculatedDeadline()
    {
        $calculated_deadline = 0;
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            if ($this->idl->getStartingTimestamp() && $this->assignment->getRelativeDeadline()) {
                $calculated_deadline = $this->idl->getStartingTimestamp() + ($this->assignment->getRelativeDeadline() * 24 * 60 * 60);
            }
        }
        return $calculated_deadline;
    }

    /**
     * Get relative deadline
     *
     * @return int
     */
    public function getRelativeDeadline()
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            return $this->assignment->getRelativeDeadline();
        }
        return 0;
    }

    /**
     * Get relative deadline presentation
     *
     * @return string
     */
    public function getRelativeDeadlinePresentation()
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_RELATIVE) {
            return $this->getRelativeDeadline() . " " . $this->lng->txt("days");
        }
        return "";
    }

    /**
     * Get official deadline (individual deadline, fixed deadline or calculated deadline (using relative deadline and starting ts))
     *
     * Grace period is not taken into account here.
     *
     * @return int
     */
    public function getOfficialDeadline()
    {
        $dl = $this->idl->getIndividualDeadline();		// team or user individual deadline

        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_ABSOLUTE) {	// absolute deadline
            return max($this->assignment->getDeadline(), $dl);				// take what's greater: idl or abs deadline
        }

        // relative deadline: take max idl or calculated deadline
        return max($this->getCalculatedDeadline(), $dl);
    }


    /**
     * Get official deadline presentation
     *
     * @return string
     */
    public function getOfficialDeadlinePresentation()
    {
        if ($this->getOfficialDeadline() > 0) {
            return $this->getTimePresentation($this->getOfficialDeadline());
        }

        return "";
    }

    /**
     * Check if official deadline exists and has ended
     *
     * @return bool
     */
    public function exceededOfficialDeadline()
    {
        $od = $this->getOfficialDeadline();
        if ($od && $od < time()) {
            return true;
        }
        return false;
    }

    /**
     * Remaining time presentation (based on official deadline)
     *
     * @param
     * @return string
     */
    public function getRemainingTimePresentation()
    {
        $lng = $this->lng;
        $official_deadline = $this->getOfficialDeadline();
        if ($official_deadline == 0) {
            return $lng->txt("exc_no_deadline_specified");
        }
        if ($official_deadline - $this->time <= 0) {
            $time_str = $lng->txt("exc_time_over_short");
        } else {
            $time_str = ilUtil::period2String(new ilDateTime($official_deadline, IL_CAL_UNIX));
        }

        return $time_str;
    }

    /**
     * Get individual deadline
     *
     * @return int
     */
    public function getIndividualDeadline()
    {
        if ($this->idl->getIndividualDeadline() > $this->getCommonDeadline()) {
            return $this->idl->getIndividualDeadline();
        }
        return 0;
    }


    /**
     * Get common deadline presentation
     *
     * @return string
     */
    public function getIndividualDeadlinePresentation()
    {
        if ($this->getIndividualDeadline() > 0) {
            return $this->getTimePresentation($this->getIndividualDeadline());
        }

        return "";
    }

    /**
     * Get common deadline (no individual deadline or grace period included)
     *
     * @return int
     */
    public function getCommonDeadline()
    {
        if ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_ABSOLUTE) {	// absolute deadline
            return $this->assignment->getDeadline();
        }

        return $this->getCalculatedDeadline();
    }

    /**
     * Get common deadline presentation
     *
     * @return string
     */
    public function getCommonDeadlinePresentation()
    {
        if ($this->getCommonDeadline() > 0) {
            return $this->getTimePresentation($this->getCommonDeadline());
        }

        return "no deadline";
    }

    /**
     * Get effective deadline (max of official deadline and grace end period) for the user
     *
     * @return int
     */
    public function getEffectiveDeadline()
    {
        return max($this->getOfficialDeadline(), $this->assignment->getExtendedDeadline());
    }

    /**
     * Get peer review deadline
     *
     * @return int
     */
    public function getPeerReviewDeadline()
    {
        if ($this->assignment->getPeerReview() &&
            $this->assignment->getPeerReviewDeadline()) {
            return $this->assignment->getPeerReviewDeadline();
        }
        return 0;
    }

    /**
     * Get common deadline presentation
     *
     * @return string
     */
    public function getPeerReviewDeadlinePresentation()
    {
        if ($this->getPeerReviewDeadline() > 0) {
            return $this->getTimePresentation($this->getPeerReviewDeadline());
        }

        return "no peer review deadline";
    }

    /**
     * Is submission currently allowed
     *
     * @return bool
     */
    public function isPeerReviewAllowed()
    {
        if ($this->assignment->getPeerReview() && $this->hasSubmissionEndedForAllUsers()
            && ($this->getPeerReviewDeadline() == 0 || $this->getPeerReviewDeadline() > $this->time)) {
            return true;
        }

        return false;
    }

    /**
     * Get common deadline presentation
     *
     * @return string
     */
    protected function getTimePresentation($a_timestamp)
    {
        if ($a_timestamp > 0) {
            return ilDatePresentation::formatDate(new ilDateTime($a_timestamp, IL_CAL_UNIX));
        }

        return "";
    }

    /**
     * Instructions visible
     *
     * @return bool
     */
    public function areInstructionsVisible()
    {
        return $this->hasSubmissionStarted();
    }

    /**
     * Get late submission warning
     *
     * @param
     * @return
     */
    /*
    function getLateSubmissionWarning()
    {
        $lng = $this->lng;
        $late_dl = "";

        // official deadline is done, but submission still allowed
        if ($this->inLateSubmissionPhase())
        {
            // extended deadline date should not be presented anywhere
            $late_dl = $this->getTimePresentation($this->getOfficialDeadline());
            $late_dl = "<br />".sprintf($lng->txt("exc_late_submission_warning"), $late_dl);
            $late_dl = '<span class="warning">'.$late_dl.'</span>';
        }

        return $late_dl;
    }*/
    
    /**
     * In late submission phase
     *
     * @param
     * @return
     */
    public function inLateSubmissionPhase()
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
     *
     * (if the assignment is generally started and for relative deadlines, if the user started the assignment)
     *
     * @return bool
     */
    public function hasSubmissionStarted()
    {
        if ($this->hasGenerallyStarted() && ($this->assignment->getDeadlineMode() == ilExAssignment::DEADLINE_ABSOLUTE ||
                $this->getIndividualStart() > 0)) {
            return true;
        }
        return false;
    }

    /**
     * Check if the submission phase has ended for the current user
     *
     * @return bool
     */
    public function hasSubmissionEnded()
    {
        if ($this->getEffectiveDeadline() == 0) {
            return false;
        }

        if ($this->time > $this->getEffectiveDeadline()) {
            return true;
        }
        return false;
    }

    /**
     * Has submission ended for all users
     *
     * @param
     * @return
     */
    public function hasSubmissionEndedForAllUsers()
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



    /**
     * Is submission currently allowed
     *
     * @param
     * @return
     */
    public function isSubmissionAllowed()
    {
        if ($this->hasSubmissionStarted() && !$this->hasSubmissionEnded()) {
            return true;
        }
        return false;
    }
}
