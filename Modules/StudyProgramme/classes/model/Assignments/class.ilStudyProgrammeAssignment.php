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

/**
 * Represents one assignment of the user to a program tree.
 * One user can have multiple assignments to the same tree.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeAssignment
{
    public const NO_RESTARTED_ASSIGNMENT = -1;

    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    public const AUTO_ASSIGNED_BY_ROLE = -1;
    public const AUTO_ASSIGNED_BY_ORGU = -2;
    public const AUTO_ASSIGNED_BY_COURSE = -3;
    public const AUTO_ASSIGNED_BY_GROUP = -4;

    protected int $id;
    protected int $usr_id;
    protected int $root_prg_id;
    protected ?string $last_change = null;
    protected ?int $last_change_by;
    protected ?DateTimeImmutable $restart_date;
    protected ?int $restarted_asssignment_id = self::NO_RESTARTED_ASSIGNMENT;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRootId(): int
    {
        return $this->root_prg_id;
    }

    public function withRootId(int $root_prg_id): ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->root_prg_id = $root_prg_id;
        return $clone;
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function withUserId(int $usr_id): ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->usr_id = $usr_id;
        return $clone;
    }

    public function getLastChangeBy(): int
    {
        return $this->last_change_by;
    }

    public function getLastChange(): ?DateTimeImmutable
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
    ): ilStudyProgrammeAssignment {
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

    public function getRestartDate(): ?DateTimeImmutable
    {
        return $this->restart_date;
    }

    public function getRestartedAssignmentId(): int
    {
        return $this->restarted_asssignment_id;
    }

    public function withRestarted(
        int $restarted_asssignment_id,
        DateTimeImmutable $restart_date = null
    ): ilStudyProgrammeAssignment {
        $clone = clone $this;
        $clone->restarted_asssignment_id = $restarted_asssignment_id;
        $clone->restart_date = $restart_date;
        return $clone;
    }
}
