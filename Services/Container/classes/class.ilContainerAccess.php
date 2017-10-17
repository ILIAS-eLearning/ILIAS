<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilContainerAccess
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ingroup ServicesContainer
 */
class ilContainerAccess
{
	/**
	 * @param ilWACPath $ilWACPath
	 *
	 * @return bool
	 */
	public function canBeDelivered(ilWACPath $ilWACPath) {
		global $ilAccess;

		preg_match("/\\/obj_([\\d]*)\\//uism", $ilWACPath->getPath(), $results);
		foreach (ilObject2::_getAllReferences($results[1]) as $ref_id) {
			if ($ilAccess->checkAccess('read', '', $ref_id)) {
				return true;
			}
		}

		return false;
	}
}

?>