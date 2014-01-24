<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjBlogAdministration
* 
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id:$
*
* @package ModulesBlog
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjBlogAdministration extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "blga";
		$this->ilObject($a_id,$a_call_by_reference);

		$this->lng->loadLanguageModule("blog");
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
