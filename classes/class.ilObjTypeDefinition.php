<?php
/**
* Class ilObjTypeDefinition
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObject
* @package ilias-core
*/
class ilObjTypeDefinition extends ilObject
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjTypeDefinition($a_id = 0,$a_call_by_reference = false)
	{
		$this->ilObject($a_id,$a_call_by_reference);
		$this->type = "typ";
	}


}
?>
