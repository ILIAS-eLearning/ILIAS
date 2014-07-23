<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/WebAccessChecker/interfaces/interface.ilComponentWebAccessChecker.php";

/**
* Class ilFileSystemStorageWebAccessChecker
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @ingroup ServicesFileSystem
*/
abstract class ilFileSystemStorageWebAccessChecker implements ilComponentWebAccessChecker
{
	protected $object_id; // [int]
	
	public function isValidPath(array $a_path)
	{
		// last element is file		
		array_pop($a_path);
		
		// 2nd to last: directory with object id
		$dir = array_pop($a_path);
		
		// extract id from directory title
		$obj_id = (int)array_pop(explode("_", $dir));	
		if((int)$obj_id)
		{
			$this->object_id = $obj_id;
			return true;
		}
	}
	
	public function getRepositoryObjectId()
	{
		return $this->object_id;
	}	
	
	public function checkAccess(array $a_user_ids)
	{
		
	}
}
