<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLicense
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilLicense.php $
* 
* @package ilias-license
*/


class ilLicenseAccess
{
	/**
	* Check, if licencing is enabled
	* This check is called from the ilAccessHandler class.
	*
	* @return   boolean     licensing enabled (true/false)
	*/
	static function _isEnabled()
	{
		static $enabled;

		if (isset($enabled))
		{
			return $enabled;
		}

		$lic_set = new ilSetting("license");
		if ($lic_set->get("license_counter"))
		{
			$enabled = true;
			return true;
		}
		else
		{
			$enabled = false;
			return false;
		}
	}
	
	
	/**
	* Check, if a user can access an object by license
	*
	* The user can access, if he/she already accessed the object
	* or if a license is available for the object.
	* This check is called from the ilAccessHandler class.
	*
	* @access   static
	* @param    int     	user id
	* @param    int     	object id (not reference)
	* @return   boolean     access is possible (true/false)
	*/
	static function _checkAccess($a_usr_id, $a_obj_id)
	{
		global $ilDB, $ilUser;

		// check the object license
		$query = 'SELECT licenses, used FROM license_data WHERE obj_id = %s';
		$result = $ilDB->queryF($query, array('integer'), array($a_obj_id));

		if ($row = $ilDB->fetchObject($result))
		{
			// no licenses set or at least one free => grant access
			if ($row->licenses == 0
			or  $row->used < $row->licenses)
			{
				return true;
			}
		}
		else
		{
			// no license data available => access granted
			return true;
		}

		// check if user has already accessed
		$query = 'SELECT read_count FROM read_event '
				.'WHERE usr_id = %s AND obj_id = %s';
			
		$result = $ilDB->queryF($query,
						array('integer','integer'),
						array($a_usr_id, $a_obj_id));
						
		if ($row = $ilDB->fetchObject($result))
		{
			return true;
		}
		
		// all failed
		return false;
	}
}
		
?>
