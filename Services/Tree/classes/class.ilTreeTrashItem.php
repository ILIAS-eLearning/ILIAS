<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 *
 */
class ilTreeTrashItem
{
    /**
     * @var int
     */
    private $deleted_by;

    /**
     * @var string
     */
    private $deleted;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $ref_id;

    /**
     * @var int
     */
    private $obj_id;

    /**
     * ilTreeTrashItem constructor.
     */
    public function __construct()
    {
    }


    /**
     * @param int $obj_id
     */
    public function setObjId(int $obj_id)
    {
        $this->obj_id = $obj_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @param int $ref_id
     */
    public function setRefId(int $ref_id)
    {
        $this->ref_id = $ref_id;
    }

    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }


    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @param string $description
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $deleted
     */
    public function setDeleted(?string $deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return string
     */
    public function getDeleted() : ?string
    {
        return $this->deleted;
    }


    /**
     * @param int $deleted_by
     */
    public function setDeletedBy(int $deleted_by)
    {
        $this->deleted_by = $deleted_by;
    }

    /**
     * @return int
     */
    public function getDeletedBy() : int
    {
        return $this->deleted_by;
    }
}
