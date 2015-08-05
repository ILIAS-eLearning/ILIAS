<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilObjectTableGUI.php';

/**
 * GUI class for the workflow of copying objects
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ingroup ServicesObject
 */
class ilObjectCopyCourseGroupSelectionTableGUI extends ilObjectTableGUI
{
	
	/**
	 * Set objects
	 * @param type $a_obj_ids
	 */
	public function setObjects($a_obj_ids)
	{
		$ref_ids = array();
		foreach($a_obj_ids as $obj_id)
		{
			$all_ref_ids = ilObject::_getAllReferences($obj_id);
			$ref_ids[] = end($all_ref_ids);
		}
		return parent::setObjects($ref_ids);
	}
}
?>
