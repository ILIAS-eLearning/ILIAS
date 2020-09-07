<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents a condition trigger object
 *
 * @author killing@leifos.de
 * @ingroup ServicesCondition
 */
class ilConditionTrigger
{
    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $type;

    /**
     * Constructor
     */
    public function __construct($ref_id, $obj_id, $obj_type)
    {
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
        $this->type = $obj_type;
    }

    /**
     * Get ref id
     *
     * @return int ref id
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * Get obj id
     *
     * @return int obj id
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get type
     *
     * @return string type
     */
    public function getType()
    {
        return $this->type;
    }
}
