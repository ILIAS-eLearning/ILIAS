<?php
/**
* Class ilObjLanguageGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.LanguageObjectOut.php,v 1.2 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends ilObject
* @package ilias-core
*/

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
