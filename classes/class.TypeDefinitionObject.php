<?php
/**
* Class TypeDefinitionObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/
class TypeDefinitionObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function TypeDefinitionObject($a_id = 0,$a_call_by_reference = false)
	{
		$this->Object($a_id,$a_call_by_reference);
		$this->type = "typ";
	}


} // END class.TypeDefinitionObject
?>
