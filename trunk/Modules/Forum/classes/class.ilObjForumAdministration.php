<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjForumAdministration
* 
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
*
* @extends ilObject
* @package ilias-core
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjForumAdministration extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "frma";
		parent::__construct($a_id,$a_call_by_reference);

		$this->lng->loadLanguageModule('forum');
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	public function update()
	{
		if (!parent::update())
		{			
			return false;
		}

		// put here object specific stuff
		
		return true;
	}

} 
?>
