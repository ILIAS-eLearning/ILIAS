<?php
/**
* Class LanguageObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.LanguageObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/

class LanguageObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function LanguageObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "lng";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}
} // END class.LanguageObjectOut
?>