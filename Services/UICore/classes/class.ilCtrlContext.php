<?php

/**
 * Class ilCtrlContext is a data transfer object to
 * pass along context information.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlContext
{
    /**
     * @var int|null
     */
    private ?int $obj_id;

    /**
     * @var string|null
     */
    private ?string $obj_type;

    /**
     * @var int|null
     */
    private ?int $sub_obj_id;

    /**
     * @var string|null
     */
    private ?string $sub_obj_type;

    /**
     * ilCtrlContext constructor
     *
     * @param int|null    $obj_id
     * @param string|null $obj_type
     * @param int|null    $sub_obj_id
     * @param string|null $sub_obj_type
     */
    public function __construct(
        int $obj_id = null,
        string $obj_type = null,
        int $sub_obj_id = null,
        string $sub_obj_type = null
    ) {
        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;
        $this->sub_obj_id = $sub_obj_id;
        $this->sub_obj_type = $sub_obj_type;
    }

    /**
     * @return int|null
     */
    public function getObjId() : ?int
    {
        return $this->obj_id;
    }

    /**
     * @param int $obj_id
     * @return ilCtrlContext
     */
    public function setObjId(int $obj_id) : ilCtrlContext
    {
        $this->obj_id = $obj_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getObjType() : ?string
    {
        return $this->obj_type;
    }

    /**
     * @param string $obj_type
     * @return ilCtrlContext
     */
    public function setObjType(string $obj_type) : ilCtrlContext
    {
        $this->obj_type = $obj_type;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSubObjId() : ?int
    {
        return $this->sub_obj_id;
    }

    /**
     * @param int|null $sub_obj_id
     * @return ilCtrlContext
     */
    public function setSubObjId(?int $sub_obj_id) : ilCtrlContext
    {
        $this->sub_obj_id = $sub_obj_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubObjType() : ?string
    {
        return $this->sub_obj_type;
    }

    /**
     * @param string|null $sub_obj_type
     * @return ilCtrlContext
     */
    public function setSubObjType(?string $sub_obj_type) : ilCtrlContext
    {
        $this->sub_obj_type = $sub_obj_type;
        return $this;
    }
}