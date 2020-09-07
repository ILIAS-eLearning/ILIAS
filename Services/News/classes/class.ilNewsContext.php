<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class ilNewsContext
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $obj_type;

    /**
     * @var int
     */
    protected $sub_id;

    /**
     * @var string
     */
    protected $sub_type;

    /**
     * Constructor
     */
    public function __construct(int $obj_id, string $obj_type, int $sub_id, string $sub_type)
    {
        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;
        $this->sub_id = $sub_id;
        $this->sub_type = $sub_type;
    }

    /**
     * Get Obj Id
     *
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * Get Obj Type.
     *
     * @return string
     */
    public function getObjType() : string
    {
        return $this->obj_type;
    }

    /**
     * Get Sub Obj Id.
     *
     * @return int
     */
    public function getSubId() : int
    {
        return $this->sub_id;
    }

    /**
     * Get Sub Obj Type.
     *
     * @return string
     */
    public function getSubType() : string
    {
        return $this->sub_type;
    }
}
