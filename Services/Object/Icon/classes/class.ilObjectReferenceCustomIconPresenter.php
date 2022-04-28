<?php declare(strict_types=1);

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
