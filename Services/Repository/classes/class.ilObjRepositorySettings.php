<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject.php");

/**
 * Class ilObjRepositorySettings
 * 
 * @author Stefan Meyer <meyer@leifos.com> 
 * @version $Id: class.ilObjSystemFolder.php 33501 2012-03-03 11:11:05Z akill $
 * 
 * @ingroup ServicesRepository
 */
class ilObjRepositorySettings extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id,$a_call_by_reference = true)
	{
		$this->type = "reps";
		parent::__construct($a_id,$a_call_by_reference);
	}

	function delete()
	{
		// DISABLED
		return false;
	}
}
	
?>