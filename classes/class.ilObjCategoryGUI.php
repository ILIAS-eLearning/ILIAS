<?php
/**
* Class ilObjCategoryGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.CategoryObjectOut.php,v 1.3 2003/03/13 17:48:30 akill Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

class ilObjCategoryGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjCategoryGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "cat";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
} // END class.LeraningObject
?>
