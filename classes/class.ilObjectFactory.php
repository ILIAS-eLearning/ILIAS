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
* Class ilObjectFactory
*
* This class offers methods to get instances of
* the type-specific object classes (derived from
* ilObject) by their object or reference id
*
* Note: The term "Ilias objects" means all
* object types that are stored in the
* database table "object_data"
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
class ilObjectFactory
{
	/**
	* get an instance of an Ilias object by object id
	*
	* @param	int		$obj_id		object id
	* @return	object	instance of Ilias object (i.e. derived from ilObject)
	*/
	function getInstanceByObjId($a_obj_id)
	{
		global $ilias, $log, $objDefinition;

		// check object id
		if (!isset($a_obj_id))
		{
			$message = "ilObjectFactory::getInstanceByObjId(): No obj_id given!";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		// read object data
		$q = "SELECT * FROM object_data ".
			 "WHERE obj_id = '".$a_obj_id."'";
		$object_set = $ilias->db->query($q);

		// check number of records
		if ($object_set->numRows() == 0)
		{
			$message = "ilObjectFactory::getInstanceByObjId(): Object with obj_id: ".$a_obj_id." not found!";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		$object_rec = $object_set->fetchRow(DB_FETCHMODE_ASSOC);
		$class_name = "ilObj".$objDefinition->getClassName($object_rec["type"]);

		// check class
		if ($class_name == "ilObj")
		{
			$message = "ilObjectFactory::getInstanceByObjId(): Not able to determine object ".
				"class for type".$object_rec["type"].".";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		// get module
		$module = $objDefinition->getModule($object_rec["type"]);
		$module_dir = ($module == "")
			? ""
			: $module."/";

		// create instance
		require_once($module_dir."classes/class.".$class_name.".php");
		$obj =& new $class_name(0, false);	// this avoids reading of data
		$obj->setId($a_obj_id);
		$obj->setObjDataRecord($object_rec);
		$obj->read();

		return $obj;
	}


	/**
	* get an instance of an Ilias object by reference id
	*
	* @param	int		$obj_id		object id
	* @return	object	instance of Ilias object (i.e. derived from ilObject)
	*/
	function getInstanceByRefId($a_ref_id)
	{
		global $ilias, $log, $objDefinition;

		// check reference id
		if (!isset($a_ref_id))
		{
			$message = "ilObjectFactory::getInstanceByRefId(): No ref_id given!";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		// read object data
		$q = "SELECT * FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id ".
		 	"WHERE object_reference.ref_id='".$a_ref_id."'";
		$object_set = $ilias->db->query($q);

		// check number of records
		if ($object_set->numRows() == 0)
		{
			$message = "ilObjectFactory::getInstanceByRefId(): Object with ref_id ".$a_ref_id." not found!";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		$object_rec = $object_set->fetchRow(DB_FETCHMODE_ASSOC);
		$class_name = "ilObj".$objDefinition->getClassName($object_rec["type"]);

		// check class
		if ($class_name == "ilObj")
		{
			$message = "ilObjectFactory::getInstanceByRefId(): Not able to determine object ".
				"class for type".$object_rec["type"].".";
			$log->writeWarning($message);
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		// get module
		$module = $objDefinition->getModule($object_rec["type"]);
		$module_dir = ($module == "")
			? ""
			: $module."/";

		// create instance
		require_once($module_dir."classes/class.".$class_name.".php");
		$obj =& new $class_name(0, false);	// this avoids reading of data
		$obj->setId($object_rec["obj_id"]);
		$obj->setRefId($a_ref_id);
		$obj->setObjDataRecord($object_rec);
		$obj->read();

		return $obj;
	}

}

?>
