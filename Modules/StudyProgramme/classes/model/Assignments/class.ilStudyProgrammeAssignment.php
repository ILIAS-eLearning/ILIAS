<?php declare(strict_types = 1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Represents one assignment of the user to a program tree.
 * One user can have multiple assignments to the same tree.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */

class ilStudyProgrammeAssignment
{
    const NO_RESTARTED_ASSIGNMENT = -1;

    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';

    const AUTO_ASSIGNED_BY_ROLE = -1;
    const AUTO_ASSIGNED_BY_ORGU = -2;
    const AUTO_ASSIGNED_BY_COURSE = -3;
    const AUTO_ASSIGNED_BY_GROUP = -4;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $root_prg_id;

    /**
     * @var int
     */
    protected $last_change;

    /**
     * @var int
     */
    protected $last_change_by;

    /**
     * @var DateTime | null
     */
    protected $restart_date;

    /**
     * @var int
     */
    protected $restarted_asssignment_id = self::NO_RESTARTED_ASSIGNMENT;


    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getRootId() : int
    {
        return $this->root_prg_id;
    }

    public function withRootId(int $root_prg_id) : ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->root_prg_id = $root_prg_id;
        return $clone;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function withUserId(int $usr_id) : ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->usr_id = $usr_id;
        return $clone;
    }

    public function getLastChangeBy() : int
    {
        return $this->last_change_by;
    }

    public function withLastChangeBy(int $last_change_by) : ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->last_change_by = $last_change_by;
        return $clone;
    }

    public function getLastChange() : DateTime //TODO: use DateTimeImmutable
    {
        $d = DateTime::createFromFormat(self::DATE_TIME_FORMAT, $this->last_change);
        if (!$d) { //TODO: should not happen, better throw...
            return new DateTime();
        }
        return $d;
    }

    public function withLastChange(DateTimeImmutable $last_change) : ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->last_change = $last_change->format(self::DATE_TIME_FORMAT);
        return $clone;
    }

    public function withRestartDate(DateTimeImmutable $date = null) : ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->restart_date = $date;
        return $clone;
    }

    public function getRestartDate() : ?DateTimeImmutable
    {
        return $this->restart_date;
    }

    public function getRestartedAssignmentId() : int
    {
        return $this->restarted_asssignment_id;
    }

    public function withRestartedAssignmentId(int $id) : ilStudyProgrammeAssignment
    {
        $clone = clone $this;
        $clone->restarted_asssignment_id = $id;
        return $clone;
    }
}
