<?php
/**
* Class RoleFolderObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id$
* 
* @extends Object
* @package ilias-core
*/

class RoleFolderObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function RoleFolderObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
	}

	function adoptPermSaveObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=perm");
		exit();
	}

} // END class.RoleFolderObjectOut
?>