<?php
/**
* Class ilObjUserFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/
class ilObjUserFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjUserFolder($a_id,$a_call_by_reference = true)
	{
		$this->type = "usrf";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	function getSubObjects()
	{
		return false;
	}
} // class
?>
