<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectReferenceCustomIconPresenter implements ilObjectCustomIconPresenter
{
    private ilObjectCustomIconFactory $factory;
    private ?ilObjectCustomIcon $icon = null;
    private int $obj_id;

    public function __construct(int $obj_id, ilObjectCustomIconFactory $factory)
    {
        $this->factory = $factory;
        $this->obj_id = $obj_id;
    }

    /**
     * Init ilObjectCustomIconPresenter
     * If the target is invalid the icon instance
     * creation is based on the reference object obj_id
     */
    public function init() : void
    {
        $target_obj_id = $this->lookupTargetId();
        $this->icon = $this->factory->getByObjId($target_obj_id);
    }

    public function exists() : bool
    {
        return $this->icon->exists();
    }

    public function getFullPath() : string
    {
        return $this->icon->getFullPath();
    }

    protected function lookupTargetId() : int
    {
        return ilContainerReference::_lookupTargetId($this->obj_id);
    }
}
