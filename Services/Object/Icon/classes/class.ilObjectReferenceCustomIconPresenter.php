<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectCustomIconPresenter
 */
class ilObjectReferenceCustomIconPresenter implements \ilObjectCustomIconPresenter
{
    /** @var \ilObjectCustomIconFactory*/
    private $factory = null;
    
    /** @var \ilObjectCustomIcon */
    private $icon = null;

    /** @var int */
    private $obj_id = 0;

    /**
     * ilObjectReferenceCustomIconPresenter constructor.
     * @param int                       $obj_id
     * @param ilObjectCustomIconFactory $factory
     */
    public function __construct(int $obj_id, \ilObjectCustomIconFactory $factory)
    {
        $this->factory = $factory;
        $this->obj_id = $obj_id;
    }

    /**
     * Init \ilObjectCustomIconPresenter
     * If the target is invalid the icon instance
     * creation is based on the reference object obj_id
     */
    public function init()
    {
        $target_obj_id = $this->lookupTargetId();
        $this->icon = $this->factory->getByObjId($target_obj_id);
    }



    /**
     * @inheritdoc
     */
    public function exists() : bool
    {
        return $this->icon->exists();
    }

    /**
     * @inheritdoc
     */
    public function getFullPath() : string
    {
        return $this->icon->getFullPath();
    }

    /**
     * Lookup target id of container reference
     * @return int
     */
    protected function lookupTargetId() : int
    {
        $target_obj_id = ilContainerReference::_lookupTargetId($this->obj_id);
        return $target_obj_id;
    }
}
