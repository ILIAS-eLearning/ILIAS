<?php
/**
* Class ilObjLanguageGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjLanguageGUI.php,v 1.1 2003/03/24 15:41:43 akill Exp $
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjLanguageGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjLanguageGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "lng";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
} // END class.LanguageObjectOut
?>
