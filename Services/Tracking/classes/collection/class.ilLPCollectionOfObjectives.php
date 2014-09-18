<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Tracking/classes/collection/class.ilLPCollection.php";

/**
* LP collection of objectives
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPCollections.php 40326 2013-03-05 11:39:24Z jluetzen $
*
* @ingroup ServicesTracking
*/
class ilLPCollectionOfObjectives extends ilLPCollection
{
	protected function read($a_obj_id)
	{
		include_once 'Modules/Course/classes/class.ilCourseObjective.php';
	
		$this->items = ilCourseObjective::_getObjectiveIds($a_obj_id,true);	
	}	
	
	public function hasSelectableItems()
	{
		return false;
	}
}

?>