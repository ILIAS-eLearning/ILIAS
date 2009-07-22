<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "classes/class.ilObject.php";

/**
* Class ilObjSearchSettings
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/
class ilObjSearchSettings extends ilObject
{
	/**
	* @var Settings object
	*/
	var $settings_obj = null;


	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSearchSettings($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "seas";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	function initSettingsObject()
	{
		include_once 'Services/Search/classes/class.ilSearchSettings.php';

		$this->settings_obj = new ilSearchSettings();
	}



	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		if (!parent::update())
		{			
			return false;
		}

		// put here object specific stuff
		
		return true;
	}

	

	
} // END class.ilObjSearchSettings
?>
