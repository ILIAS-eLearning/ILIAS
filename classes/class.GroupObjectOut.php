<?php
/**
* Class GroupObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.GroupObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/

class GroupObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function GroupObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "grp";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}
} // END class.GroupObjectOut
?>