<?php
/**
* Class ilObjCategory
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjCategory extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjCategory($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "cat";
		$this->ilObject($a_id,$a_call_by_reference);
	}
} // END class.CategoryObject
?>
