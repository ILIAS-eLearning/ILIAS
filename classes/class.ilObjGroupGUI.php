<?php
/**
* Class ilObjGroupGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjGroupGUI.php,v 1.1 2003/03/24 15:41:43 akill Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjGroupGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjGroupGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "grp";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
} // END class.GroupObjectOut
?>
