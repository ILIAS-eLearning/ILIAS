<?php
/**
* Class ilObjSystemFolder
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjSystemFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSystemFolder($a_id,$a_call_by_reference = true)
	{
		$this->type = "adm";
		$this->ilObject($a_id,$a_call_by_reference);
	}
} // END class.SystemFolderObject
?>
