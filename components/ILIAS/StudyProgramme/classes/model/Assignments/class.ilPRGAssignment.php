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

use ILIAS\StudyProgramme\Assignment\Zipper;
use ILIAS\StudyProgramme\Assignment\Node;

/**
 * Assignments are relations of users to a PRG;
 * They hold progress-information for (sub-)nodes of the PRG-tree.
 */
class ilPRGAssignment
{
    use ilPRGAssignmentActions;

    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    public const NO_RESTARTED_ASSIGNMENT = -1;

    public const AUTO_ASSIGNED_BY_ROLE = -1;
    public const AUTO_ASSIGNED_BY_ORGU = -2;
    public const AUTO_ASSIGNED_BY_COURSE = -3;
    public const AUTO_ASSIGNED_BY_GROUP = -4;

    protected int $id;
    protected int $usr_id;
    protected ?\DateTimeImmutable $last_change = null;
    protected ?int $last_change_by = null;
    protected ?\DateTimeImmutable$restart_date = null;
    protected ?int $restarted_asssignment_id = self::NO_RESTARTED_ASSIGNMENT;
    protected bool $manually_assigned;
    /**
     * @var array <prg_obj_id, Progress>
     */
    protected array $progresses = [];
    protected ilPRGProgress $progress;
    protected ilPRGUserInformation $user_info;
    protected StudyProgrammeEvents $events;

    public function __construct(int $id, int $usr_id)
    {
        $this->id = $id;
        $this->usr_id = $usr_id;
        $this->events = new PRGNullEvents();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function getLastChangeBy(): int
    {
        return $this->last_change_by;
    }

    public function getLastChange(): ?\DateTimeImmutable
    {
        return $this->last_change;
    }

    public function withLastChange(
        int $last_change_by,
        \DateTimeImmutable $last_change
    ): self {
        if ($this->getLastChange()
            && $this->getLastChange()->format(self::DATE_TIME_FORMAT) > $last_change->format(self::DATE_TIME_FORMAT)
        ) {
            throw new ilException(
                "Cannot set last change to an earlier date:"
                . "\ncurrent: " . $this->getLastChange()->format(self::DATE_TIME_FORMAT)
                . "\nnew: " . $last_change,
                1
            );
        }
        $clone = clone $this;
        $clone->last_change = $last_change;
        $clone->last_change_by = $last_change_by;
        return $clone;
    }

    public function getRestartDate(): ?\DateTimeImmutable
    {
        return $this->restart_date;
    }

    public function getRestartedAssignmentId(): int
    {
        return $this->restarted_asssignment_id;
    }

    public function isRestarted(): bool
    {
        return $this->getRestartedAssignmentId() !== -1;
    }

    public function withRestarted(
        int $restarted_asssignment_id,
        \DateTimeImmutable $restart_date = null
    ): self {
        $clone = clone $this;
        $clone->restarted_asssignment_id = $restarted_asssignment_id;
        $clone->restart_date = $restart_date;
        return $clone;
    }

    public function isManuallyAssigned(): bool
    {
        return $this->manually_assigned;
    }

    public function withManuallyAssigned(bool $manual): self
    {
        $clone = clone $this;
        $clone->manually_assigned = $manual;
        return $clone;
    }

    public function withUserInformation(ilPRGUserInformation $user_info): self
    {
        $clone = clone $this;
        $clone->user_info = $user_info;
        return $clone;
    }
    public function getUserInformation(): ilPRGUserInformation
    {
        return $this->user_info;
    }


    public function withProgressTree(ilPRGProgress $progress): self
    {
        $clone = clone $this;
        $clone->progress = $progress;
        return $clone;
    }
    public function getProgressTree(): ilPRGProgress
    {
        return $this->progress;
    }

    public function getRootId(): int
    {
        return $this->progress->getNodeId();
    }

    public function getProgresses(array &$ret = [], ilPRGProgress $pgs = null): array
    {
        if (!$pgs) {
            $pgs = $this->getProgressTree();
        }

        $ret[] = $pgs;
        foreach ($pgs->getSubnodes() as $id => $sub) {
            $this->getProgresses($ret, $sub);
        }
        return $ret;
    }

    public function getProgressForNode(int $node_id): ilPRGProgress
    {
        $pgs = $this->getProgressTree();
        $path = $pgs->findSubnodePath((string) $node_id);

        foreach ($path as $hop) {
            if ($pgs->getId() !== $hop) {
                $pgs = $pgs->getSubnode($hop);
            }
        }
        return $pgs;
    }

    public function getProgressesWithDeadline(
        DateTimeImmutable $deadline
    ): array {
        return array_values(array_filter(
            $this->getProgresses(),
            fn ($pgs) => ! is_null($pgs->getDeadline()) && $pgs->getDeadline() <= $deadline
        ));
    }

    public function withEvents(StudyProgrammeEvents $events): self
    {
        $clone = clone $this;
        $clone->events = $events;
        return $clone;
    }

    public function getEvents(): StudyProgrammeEvents
    {
        return $this->events;
    }
}
