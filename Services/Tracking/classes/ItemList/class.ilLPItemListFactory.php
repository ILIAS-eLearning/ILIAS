<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilLPItemListGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

class ilLPItemListFactory
{
	function &_getInstance($a_parent_id,$a_id,$a_type)
	{
		static $obj_cache = array();
		
		if(is_object($obj_cache[$a_type.'_'.$a_id]))
		{
			$object =& $obj_cache[$a_type.'_'.$a_id];
			if($a_type == 'sahs_item' or
			   $a_type == 'objective')
			{
				$object->setChildId($a_id);
			}
			return $obj_cache[$a_type.'_'.$a_id];
		}

		switch($a_type)
		{
			case 'crs':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPCourseListGUI.php';

				$object = new ilLPCourseListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'tst':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPTestListGUI.php';

				$object = new ilLPTestListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'lm':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPContentObjectListGUI.php';

				$object = new ilLPContentObjectListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'sahs':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPSAHSListGUI.php';

				$object = new ilLPSAHSListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'htlm':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPFileBasedLMListGUI.php';

				$object = new ilLPFileBasedLMListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'grp':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPGroupListGUI.php';

				$object = new ilLPGroupListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'fold':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPFolderListGUI.php';

				$object = new ilLPFolderListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'exc':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPExerciseListGUI.php';

				$object = new ilLPExerciseListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'sess':
			case 'event':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPEventListGUI.php';
				$object = new ilLPEventListGUI($a_id);
				$object->read();
				return $obj_cache[$a_type.'_'.$a_id] =& $object;

			case 'objective':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPObjectiveItemListGUI.php';
				
				$object = new ilLPObjectiveItemListGUI($a_parent_id);
				$object->setChildId($a_id);
				$object->read();
				$obj_cache[$a_type.'_'.$a_id] =& $object;
				return $obj_cache[$a_type.'_'.$a_id];

			case 'sahs_item':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPSAHSItemListGUI.php';
				
				$object = new ilLPSAHSItemListGUI($a_parent_id);
				$object->setChildId($a_id);
				$object->read();
				$obj_cache[$a_type.'_'.$a_id] =& $object;
				return $obj_cache[$a_type.'_'.$a_id];

			case 'usr':
				include_once 'Services/Tracking/classes/ItemList/class.ilLPUserItemListGUI.php';

				$object = new ilLPUserItemListGUI($a_id);
				$object->read();
				$obj_cache[$a_type.'_'.$a_id] =& $object;
				return $obj_cache[$a_type.'_'.$a_id];
				

			default:
				die('ilLPItemListFactory:: Unknown type'.$a_type);
				
		}
	}

	function &_getInstanceByRefId($a_parent_id,$a_id,$a_type)
	{
		global $ilObjDataCache;

		switch($a_type)
		{
			case 'sahs_item':
			case 'objective':
				return ilLPItemListFactory::_getInstance($ilObjDataCache->lookupObjId($a_parent_id),$a_id,$a_type);
			case 'event':
				return ilLPItemListFactory::_getInstance($a_parent_id,$a_id,$a_type);
			default:
				$object =& ilLPItemListFactory::_getInstance($a_parent_id,$ilObjDataCache->lookupObjId($a_id),$a_type);
				$object->setRefId($a_id);
				return $object;
		}
	}
}
?>