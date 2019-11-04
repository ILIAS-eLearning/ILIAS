<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise ui request
 *
 * @author killing@leifos.de
 */
class ilExerciseUIRequest
{
    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * @var int
     */
    protected $requested_ass_id;

    /**
     * @var int
     */
    protected $requested_member_id;

    /**
     * @var ilExAssignment|null
     */
    protected $ass = null;

    /**
     * @var ilObjExercise
     */
    protected $exc = null;

    /**
     * Constructor
     */
    public function __construct(array $query_params, array $post_data)
    {
        $this->requested_ref_id = (int) $query_params["ref_id"];
        $this->requested_ass_id = ($post_data["ass_id"])
            ? (int) $post_data["ass_id"]
            : (int) $query_params["ass_id"];
        $this->requested_member_id = ($post_data["member_id"])
            ? (int) $post_data["member_id"]
            : (int) $query_params["member_id"];

        if ($this->getRequestedAssId() > 0) {
            $this->ass = new ilExAssignment($this->getRequestedAssId());
        }
        if ($this->getRequestedRefId() > 0 && ilObject::_lookupType($this->getRequestedRefId(), true) == "exc") {
            $this->exc = new ilObjExercise($this->getRequestedRefId());
        }
    }

    /**
     * @return int
     */
    public function getRequestedRefId(): int
    {
        return $this->requested_ref_id;
    }

    /**
     * @return int
     */
    public function getRequestedAssId(): int
    {
        return $this->requested_ass_id;
    }

    /**
     * @return int
     */
    public function getRequestedMemberId(): int
    {
        return $this->requested_member_id;
    }

    /**
     * @return ilObjExercise|null
     */
    public function getRequestedExercise()
    {
        return $this->exc;
    }

    /**
     * @return ilExAssignment|null
     */
    public function getRequestedAssignment()
    {
        return $this->ass;
    }

}