<?php
/**
* Class ilObjGroupGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.GroupObjectOut.php,v 1.2 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

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
