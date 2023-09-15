<?php

declare(strict_types=1);

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

use ILIAS\StudyProgramme\Assignment\Node;

/**
 * A Progress is the status of a user on a single node of an assignment;
 * it is unique by assignment_id:usr_id:node_id
 */
class ilPRGProgress extends Node
{
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_ACCREDITED = 3;
    public const STATUS_NOT_RELEVANT = 4;
    public const STATUS_FAILED = 5;

    public static $STATUS = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_ACCREDITED,
        self::STATUS_NOT_RELEVANT,
        self::STATUS_FAILED
    ];

    public const COMPLETED_BY_SUBNODES = -2;

    public const DATE_TIME_FORMAT = ilPRGAssignment::DATE_TIME_FORMAT;
    public const DATE_FORMAT = ilPRGAssignment::DATE_FORMAT;

    protected int $prg_obj_id;
    protected int $points = 0;
    protected int $points_cur = 0;
    protected int $status = 4;
    protected ?int $completion_by = null;
    protected \DateTimeImmutable $last_change;
    protected int $last_change_by = -1;
    protected ?\DateTimeImmutable $assignment_date = null;
    protected ?\DateTimeImmutable $completion_date = null;
    protected ?\DateTimeImmutable $deadline = null;
    protected ?\DateTimeImmutable $vq_date = null;
    protected bool $invalidated = false;
    protected bool $is_individual = false;


    public function __construct(
        int $prg_obj_id,
        int $status = self::STATUS_NOT_RELEVANT
    ) {
        $this->prg_obj_id = $prg_obj_id;
        $this->status = $status;
        $this->last_change = new \DateTimeImmutable();
        $this->assignment_date = new \DateTimeImmutable();
        parent::__construct((string) $prg_obj_id, []);
    }

    public function getNodeId(): int
    {
        return $this->prg_obj_id;
    }


    public function getAmountOfPoints(): int
    {
        return $this->points;
    }

    public function withAmountOfPoints(int $points): self
    {
        if ($points < 0) {
            throw new ilException("ilPRGProgress::setAmountOfPoints: "
                                 . "Expected a number >= 0 as argument, got '$points'");
        }

        $clone = clone $this;
        $clone->points = $points;
        return $clone;
    }

    public function getCurrentAmountOfPoints(): int
    {
        return $this->points_cur;
    }

    public function withCurrentAmountOfPoints(int $points_cur): self
    {
        if ($points_cur < 0) {
            throw new ilException("ilPRGProgress::setAmountOfPoints: "
                                 . "Expected a number >= 0 as argument, got '$points'");
        }

        $clone = clone $this;
        $clone->points_cur = $points_cur;
        return $clone;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function withStatus(int $status): self
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

    public function isTransitionAllowedTo(int $new_status): bool
    {
        return is_null($this->status) ||
            $this->status === $new_status ||
            in_array($new_status, self::getAllowedTargetStatusFor($this->status));
    }

    public static function getAllowedTargetStatusFor(int $status_from): array
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
                    self::STATUS_IN_PROGRESS, // deaccriditation of sub-progress might revert completion,
                    self::STATUS_NOT_RELEVANT
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

    public function getLastChangeBy(): int
    {
        return $this->last_change_by;
    }

    public function getLastChange(): \DateTimeImmutable
    {
        return $this->last_change;
    }

    public function withLastChange(
        int $last_change_by,
        \DateTimeImmutable $last_change
    ): self {
        $clone = clone $this;
        $clone->last_change = $last_change;
        $clone->last_change_by = $last_change_by;
        return $clone;
    }

    public function getAssignmentDate(): ?\DateTimeImmutable
    {
        return $this->assignment_date;
    }

    public function withAssignmentDate(?\DateTimeImmutable $assignment_date): self
    {
        $clone = clone $this;
        $clone->assignment_date = $assignment_date;
        return $clone;
    }

    public function getCompletionDate(): ?\DateTimeImmutable
    {
        return $this->completion_date;
    }

    public function getCompletionBy(): ?int
    {
        return $this->completion_by;
    }

    public function withCompletion(
        int $usr_or_obj_id = null,
        \DateTimeImmutable $completion_date = null
    ): self {
        $clone = clone $this;
        $clone->completion_by = $usr_or_obj_id;
        $clone->completion_date = $completion_date;
        return $clone;
    }

    public function getDeadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    public function withDeadline(?\DateTimeImmutable $deadline = null): self
    {
        $clone = clone $this;
        $clone->deadline = $deadline;
        return $clone;
    }

    public function getValidityOfQualification(): ?\DateTimeImmutable
    {
        return $this->vq_date;
    }

    public function withValidityOfQualification(\DateTimeImmutable $date = null): self
    {
        $clone = clone $this;
        $clone->vq_date = $date;
        return $clone;
    }

    public function hasIndividualModifications(): bool
    {
        return $this->is_individual;
    }

    public function withIndividualModifications(bool $individual): self
    {
        $clone = clone $this;
        $clone->is_individual = $individual;
        return $clone;
    }

    public function isSuccessful(): bool
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
    public function hasValidQualification(\DateTimeImmutable $now): ?bool
    {
        if (!$this->isSuccessful()) {
            return null;
        }
        return (
            is_null($this->getValidityOfQualification()) ||
            $this->getValidityOfQualification()->format(self::DATE_FORMAT) >= $now->format(self::DATE_FORMAT)
        );
    }

    public function isRelevant(): bool
    {
        return $this->getStatus() != self::STATUS_NOT_RELEVANT;
    }

    public function isFailed(): bool
    {
        return $this->getStatus() == self::STATUS_FAILED;
    }

    public function isAccredited(): bool
    {
        return $this->getStatus() == self::STATUS_ACCREDITED;
    }

    public function isInProgress(): bool
    {
        return $this->getStatus() == self::STATUS_IN_PROGRESS;
    }

    public function getAchievedPointsOfChildren(): int
    {
        $sum = 0;
        $children = $this->getSubnodes();
        foreach ($children as $child_progress) {
            if ($child_progress->isSuccessful()) {
                $sum += $child_progress->getAmountOfPoints();
            }
        }
        return $sum;
    }

    public function getPossiblePointsOfRelevantChildren(): int
    {
        $sum = 0;
        $children = $this->getSubnodes();
        foreach ($children as $child_progress) {
            if ($child_progress->isRelevant()) {
                $sum += $child_progress->getAmountOfPoints();
            }
        }
        return $sum;
    }

    public function invalidate(): self
    {
        if (!$this->vq_date) {
            throw new ilException("may not invalidate non-expired progress (no invalidation date)");
        }
        if ($this->vq_date->format(self::DATE_FORMAT) > date(self::DATE_FORMAT)) {
            $msg = $this->vq_date->format(self::DATE_FORMAT) . ' > ' . date(self::DATE_FORMAT);
            throw new ilException("may not invalidate non-expired progress ($msg)");
        }
        return $this->withInvalidated(true);
    }

    public function isInvalidated(): bool
    {
        return $this->invalidated;
    }

    public function withInvalidated(bool $invalidated): self
    {
        $clone = clone $this;
        $clone->invalidated = $invalidated;
        return $clone;
    }

    public function markAccredited(\DateTimeImmutable $date, int $acting_usr_id): self
    {
        return $this
            ->withStatus(self::STATUS_ACCREDITED)
            ->withCompletion($acting_usr_id, $date)
            ->withLastChange($acting_usr_id, $date);
    }

    public function unmarkAccredited(\DateTimeImmutable $date, int $acting_usr_id): self
    {
        return $this
            ->withStatus(self::STATUS_IN_PROGRESS)
            ->withCompletion(null, null)
            ->withValidityOfQualification(null)
            ->withLastChange($acting_usr_id, $date);
    }

    public function markFailed(\DateTimeImmutable $date, int $acting_usr_id): self
    {
        return $this
            ->withStatus(self::STATUS_FAILED)
            ->withCompletion(null, null)
            ->withLastChange($acting_usr_id, $date);
    }

    public function markNotFailed(\DateTimeImmutable $date, int $acting_usr_id): self
    {
        return $this
            ->withStatus(self::STATUS_IN_PROGRESS)
            ->withCompletion(null, null)
            ->withLastChange($acting_usr_id, $date);
    }

    public function succeed(\DateTimeImmutable $date, int $triggering_obj_id): self
    {
        return $this
            ->withStatus(self::STATUS_COMPLETED)
            ->withCompletion($triggering_obj_id, $date)
            ->withLastChange($triggering_obj_id, $date);
    }

    public function markNotRelevant(\DateTimeImmutable $date, int $acting_usr_id): self
    {
        return $this
            ->withStatus(self::STATUS_NOT_RELEVANT)
            ->withLastChange($acting_usr_id, $date)
            ->withValidityOfQualification(null)
            ->withDeadline(null)
            ->withIndividualModifications(true);
    }

    public function markRelevant(\DateTimeImmutable $date, int $acting_usr_id): self
    {
        return $this
            ->withStatus(self::STATUS_IN_PROGRESS)
            ->withCompletion(null, null)
            ->withLastChange($acting_usr_id, $date)
            ->withIndividualModifications(true);
    }
}
