<?php
/**
* Class ForumObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ForumObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/

class ForumObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access	public
	*/
	function ForumObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "frm";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}
} // END class.ForumObject
?>