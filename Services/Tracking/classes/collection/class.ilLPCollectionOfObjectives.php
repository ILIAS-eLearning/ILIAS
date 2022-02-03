<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* LP collection of objectives
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @ingroup ServicesTracking
*/
class ilLPCollectionOfObjectives extends ilLPCollection
{
    protected function read(int $a_obj_id) : void
    {
        include_once 'Modules/Course/classes/class.ilCourseObjective.php';
        $this->items = ilCourseObjective::_getObjectiveIds($a_obj_id, true);
    }
    
    public function hasSelectableItems() : bool
    {
        return false;
    }
}
