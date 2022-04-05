<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents a condition target object
 * @author  killing@leifos.de
 * @ingroup ServicesCondition
 */
class ilConditionTarget
{
    protected int $ref_id;
    protected int $obj_id;
    protected string $type;

    /**
     * Constructor
     */
    public function __construct(int $ref_id, int $obj_id, string $obj_type)
    {
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
        $this->type = $obj_type;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getType() : string
    {
        return $this->type;
    }
}
