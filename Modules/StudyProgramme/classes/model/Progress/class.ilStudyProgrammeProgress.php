<?php declare(strict_types=1);

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
 * Class ilStudyProgrammeProgress.
 *
 * Represents the progress of a user for one program assignment on one node of the
 * program.
 *
 * The user has one progress per assignment and program node in the subtree of the
 * assigned program.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Denis Klöpfer <richard.klees@concepts-and-training.de>
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeProgress
{
    // The progress of a user on a program node can have different status that
    // determine how the node is taken into account for calculation of the learning
    // progress.
    
    // User needs to be successful in the node, but currently isn't.
    public const STATUS_IN_PROGRESS = 1;
    // User has completed the node successfully according to the program nodes mode.
    public const STATUS_COMPLETED = 2;
    // User was marked as successful in the node without actually having
    // successfully completed the program node according to his mode.
    public const STATUS_ACCREDITED = 3;
    // The user does not need to be successful in this node.
    public const STATUS_NOT_RELEVANT = 4;
    // The user does not need to be successful in this node.
    public const STATUS_FAILED = 5;

    public static array $STATUS = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_ACCREDITED,
        self::STATUS_NOT_RELEVANT,
        self::STATUS_FAILED
    ];

    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';
    public const DATE_FORMAT_ENDOFDAY = 'Y-m-d 23:59:59';

    /**
     * The id of this progress.
     *
     * This is superfluous, since a progress is unique per (assignment_id, prg_id,
     * user_id)-tuple, but ActiveRecords won't cooperate and wants one primary key
     * only. I'm sad.
     * We set a unique constraint on the three fields in the db update to get the
     * desired guarantees by the database.
     */
    protected int $id;

    /**
     * The id of the assignment this progress belongs to.
     */
    protected int $assignment_id;

    /**
     * The id of the program node this progress belongs to.
     */
    protected int $prg_id;

    /**
     * The id of the user this progress belongs to.
     */
    protected int $usr_id;

    /**
     * Amount of points the user needs to achieve in the subnodes to be successful
     * on this node. Also the amount of points a user gets by being successful on this
     * node.
     */
    protected int $points = 0;

    /**
     * Amount of points the user currently has in the subnodes of this node.
     */
    protected int $points_cur = 0;
 
    /**
     * The status this progress is in.
     */
    protected ?int $status = null;

    /**
     * The id of the object, that lead to the successful completion of this node.
     * This is either a user when status is accreditted, a course object if the mode
     * of the program node is lp_completed and the node is completed. Its null
     * otherwise.
     */
    protected ?int $completion_by = null;
    

    /**
     * The timestamp of the moment this progress was created or updated the
     * last time.
     */
    protected ?string $last_change = null;

    /**
     * Id of the user who did the last manual update of the progress
     */
    protected int $last_change_by;

    /**
     * Date of asssignment
     */
    protected ?DateTimeImmutable $assignment_date = null;

    /**
     * Date of completion
     */
    protected ?DateTimeImmutable $completion_date = null;

    /**
     * Date until user has to finish
     */
    protected ?DateTimeImmutable $deadline = null;

    /**
     * Date until which this qualification is valid.
     */
    protected ?DateTimeImmutable $vq_date = null;

    /**
     * Is this progress invalidated?
     */
    protected bool $invalidated = false;

    protected bool $is_individual = false;



    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Get the id of the progress.
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get the assignment this progress belongs to.
     */
    public function getAssignmentId() : int
    {
        return $this->assignment_id;
    }

    public function withAssignmentId(int $assignment_id) : ilStudyProgrammeProgress
    {
        $clone = clone $this;
        $clone->assignment_id = $assignment_id;
        return $clone;
    }

    /**
     * Get the obj_id of the program node this progress belongs to.
     */
    public function getNodeId() : int
    {
        return $this->prg_id;
    }

    public function withNodeId(int $prg_id) : ilStudyProgrammeProgress
    {
        $clone = clone $this;
        $clone->prg_id = $prg_id;
        return $clone;
    }

    /**
     * Get the id of the user this progress is for.
     */
    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function withUserId(int $usr_id) : ilStudyProgrammeProgress
    {
        $clone = clone $this;
        $clone->usr_id = $usr_id;
        return $clone;
    }

    /**
     * Get the amount of points the user needs to achieve on the subnodes of this
     * node. Also the amount of points, this node yields for the progress on the
     * nodes above.
     */
    public function getAmountOfPoints() : int
    {
        return $this->points;
    }
    
    /**
     * Throws when amount of points is smaller than zero.
     */
    public function withAmountOfPoints(int $points) : ilStudyProgrammeProgress
    {
        if ($points < 0) {
            throw new ilException(
                "ilStudyProgrammeProgress::setAmountOfPoints: " .
                "Expected a number >= 0 as argument, got '$points'"
            );
        }

        $clone = clone $this;
        $clone->points = $points;
        return $clone;
    }
    
    public function getCurrentAmountOfPoints() : int
    {
        return $this->points_cur;
    }
    
    /**
     * Set the amount of points the user currently has achieved on this node.
     * @throws ilException when amount of points is smaller than zero.
     */
    public function withCurrentAmountOfPoints(int $points_cur) : ilStudyProgrammeProgress
    {
        if ($points_cur < 0) {
            throw new ilException("ilStudyProgrammeProgress::setAmountOfPoints: "
                                 . "Expected a number >= 0 as argument, got '$points_cur'");
        }
        $clone = clone $this;
        $clone->points_cur = $points_cur;
        return $clone;
    }

    /**
     * Get the status the user has on this node.
     *
     * @return int - one of ilStudyProgrammeProgress::STATUS_*
     */
    public function getStatus() : int
    {
        return $this->status;
    }
    
    /**
     * Set the status of this node.
     * @throws ilException when status is none of ilStudyProgrammeProgress::STATUS_*.
     */
    public function withStatus(int $status) : ilStudyProgrammeProgress
    {
        if (!in_array($status, self::$STATUS)) {
            throw new ilException("No such status: " . "'$status'");
        }
        
        if (!$this->isTransitionAllowedTo($status)) {
            throw new ilException(
                "Changing progress with status " . $this->getStatus()
                . " cannot change to status " . "'$status'"
                . ' (progress_id: ' . $this->getId() . ')'
            );
        }

        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }
    
    public function isTransitionAllowedTo(int $new_status) : bool
    {
        return is_null($this->status) ||
            $this->status === $new_status ||
            in_array($new_status, self::getAllowedTargetStatusFor($this->status));
    }

    public static function getAllowedTargetStatusFor(int $status_from) : array
    {
        switch ($status_from) {
            case self::STATUS_IN_PROGRESS:
                return [
                    self::STATUS_ACCREDITED,
                    self::STATUS_COMPLETED,
                    self::STATUS_FAILED,
                    self::STATUS_NOT_RELEVANT
                ];
            case self::STATUS_ACCREDITED:
                return [
                    self::STATUS_IN_PROGRESS,
                    self::STATUS_COMPLETED,
                    self::STATUS_FAILED,
                    self::STATUS_NOT_RELEVANT
                ];
            case self::STATUS_COMPLETED:
                return [
                    self::STATUS_IN_PROGRESS // deaccreditation of sub-progress might revert completion,
                ];
            case self::STATUS_FAILED:
                return [
                    self::STATUS_IN_PROGRESS,
                    self::STATUS_COMPLETED, // with re-calculation of deadline, progress might directly be completed.
                    self::STATUS_NOT_RELEVANT
                ];
            case self::STATUS_NOT_RELEVANT:
                return[
                    self::STATUS_IN_PROGRESS
                ];
        }

        return [];
    }

    /**
     * Get the id of the user/object who/which invoked the last change on this assignment.
     */
    public function getLastChangeBy() : int
    {
        return $this->last_change_by;
    }
   
    public function getLastChange() : ?DateTimeImmutable
    {
        if ($this->last_change) {
            return DateTimeImmutable::createFromFormat(self::DATE_TIME_FORMAT, $this->last_change);
        }
        return null;
    }

    /**
     * @throws ilException if new date is earlier than the existing one
     */
    public function withLastChange(
        int $last_change_by,
        DateTimeImmutable $timestamp
    ) : ilStudyProgrammeProgress {
        $new_date = $timestamp->format(self::DATE_TIME_FORMAT);
        if ($this->getLastChange() && $this->getLastChange()->format(self::DATE_TIME_FORMAT) > $new_date) {
            throw new ilException(
                "Cannot set last change to an earlier date:"
                . "\ncurrent: " . $this->getLastChange()->format(self::DATE_TIME_FORMAT)
                . "\nnew: " . $new_date,
                1
            );
        }
        $clone = clone $this;
        $clone->last_change = $new_date;
        $clone->last_change_by = $last_change_by;
        return $clone;
    }

    public function getAssignmentDate() : ?DateTimeImmutable
    {
        return $this->assignment_date;
    }

    public function withAssignmentDate(DateTimeImmutable $assignment_date) : ilStudyProgrammeProgress
    {
        $clone = clone $this;
        $clone->assignment_date = $assignment_date;
        return $clone;
    }

    public function getCompletionDate() : ?DateTimeImmutable
    {
        return $this->completion_date;
    }

    /**
     * Get the id of object or user that lead to the successful completion
     * of this node.
     */
    public function getCompletionBy() : ?int
    {
        return $this->completion_by;
    }

    public function withCompletion(
        int $usr_or_obj_id = null,
        DateTimeImmutable $completion_date = null
    ) : ilStudyProgrammeProgress {
        $clone = clone $this;
        $clone->completion_by = $usr_or_obj_id;
        $clone->completion_date = $completion_date;
        return $clone;
    }
    
    public function getDeadline() : ?DateTimeImmutable
    {
        return $this->deadline;
    }

    public function withDeadline(DateTimeImmutable $deadline = null) : ilStudyProgrammeProgress
    {
        $clone = clone $this;
        $clone->deadline = $deadline;
        return $clone;
    }

    public function getValidityOfQualification() : ?DateTimeImmutable
    {
        return $this->vq_date;
    }

    public function withValidityOfQualification(DateTimeImmutable $date = null) : ilStudyProgrammeProgress
    {
        $clone = clone $this;
        $clone->vq_date = $date;
        return $clone;
    }

    public function hasIndividualModifications() : bool
    {
        return $this->is_individual;
    }

    public function withIndividualModifications(bool $individual) : ilStudyProgrammeProgress
    {
        $clone = clone $this;
        $clone->is_individual = $individual;
        return $clone;
    }

    public function isSuccessful() : bool
    {
        return in_array(
            $this->getStatus(),
            [
                self::STATUS_COMPLETED,
                self::STATUS_ACCREDITED
            ]
        );
    }
    
    /**
     * There may be no qualification at all (since the PRG is not passed),
     * or the qualification is valid or invalid due to a date.
     */
    public function hasValidQualification(DateTimeImmutable $now) : ?bool
    {
        if (!$this->isSuccessful()) {
            return null;
        }
        return (
            is_null($this->getValidityOfQualification()) ||
            $this->getValidityOfQualification()->format('Y-m-d') >= $now->format('Y-m-d')
        );
    }

    public function isRelevant() : bool
    {
        return $this->getStatus() !== self::STATUS_NOT_RELEVANT;
    }

    public function isFailed() : bool
    {
        return $this->getStatus() === self::STATUS_FAILED;
    }
    
    public function isAccredited() : bool
    {
        return $this->getStatus() === self::STATUS_ACCREDITED;
    }

    public function isInProgress() : bool
    {
        return $this->getStatus() === self::STATUS_IN_PROGRESS;
    }
    
    public function invalidate() : ilStudyProgrammeProgress
    {
        if (!$this->vq_date || $this->vq_date->format('Y-m-d') > date('Y-m-d')) {
            throw new ilException("may not invalidate non-expired progress");
        }
        $clone = clone $this;
        $clone->invalidated = true;
        return $clone;
    }
    
    public function isInvalidated() : bool
    {
        return $this->invalidated;
    }

    /**
     * @deprecated
     */
    public function isSuccessfulExpired() : bool
    {
        if (
            !is_null($this->getValidityOfQualification()) &&
            $this->getValidityOfQualification()->format('Y-m-d') < (new DateTimeImmutable())->format('Y-m-d')
        ) {
            return true;
        }
        return false;
    }

    public function markAccredited(DateTimeImmutable $date, int $acting_usr_id) : ilStudyProgrammeProgress
    {
        return $this
            ->withStatus(self::STATUS_ACCREDITED)
            ->withCompletion($acting_usr_id, $date)
            ->withLastChange($acting_usr_id, $date);
    }

    public function unmarkAccredited(DateTimeImmutable $date, int $acting_usr_id) : ilStudyProgrammeProgress
    {
        return $this
            ->withStatus(self::STATUS_IN_PROGRESS)
            ->withCompletion()
            ->withValidityOfQualification()
            ->withLastChange($acting_usr_id, $date);
    }

    public function markFailed(DateTimeImmutable $date, int $acting_usr_id) : ilStudyProgrammeProgress
    {
        return $this
            ->withStatus(self::STATUS_FAILED)
            ->withCompletion()
            ->withLastChange($acting_usr_id, $date);
    }

    public function markNotFailed(DateTimeImmutable $date, int $acting_usr_id) : ilStudyProgrammeProgress
    {
        return $this
            ->withStatus(self::STATUS_IN_PROGRESS)
            ->withCompletion()
            ->withLastChange($acting_usr_id, $date);
    }

    public function succeed(DateTimeImmutable $date, int $triggering_obj_id) : ilStudyProgrammeProgress
    {
        return $this
            ->withStatus(self::STATUS_COMPLETED)
            ->withCompletion($triggering_obj_id, $date)
            ->withLastChange($triggering_obj_id, $date);
    }

    public function markNotRelevant(DateTimeImmutable $date, int $acting_usr_id) : ilStudyProgrammeProgress
    {
        return $this
            ->withStatus(self::STATUS_NOT_RELEVANT)
            ->withLastChange($acting_usr_id, $date)
            ->withValidityOfQualification()
            ->withDeadline()
            ->withIndividualModifications(true);
    }

    public function markRelevant(DateTimeImmutable $date, int $acting_usr_id) : ilStudyProgrammeProgress
    {
        return $this
            ->withStatus(self::STATUS_IN_PROGRESS)
            ->withCompletion()
            ->withLastChange($acting_usr_id, $date)
            ->withIndividualModifications(true);
    }
}
