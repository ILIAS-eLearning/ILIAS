<?php
/**
* Class ilObjLanguageFolder
* contains all function to manage language support for ILIAS3
* install, uninstall, checkfiles ....
* 
* @author	Stefan Meyer <smeyer@databay.de>
* @version	$Id$
*
* @extends	ilObject
* @package	ilias-core
*/

require_once "class.ilObject.php";

class ilObjMail extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjMail($a_id,$a_call_by_reference = true)
	{
		$this->type = "mail";
		$this->ilObject($a_id,$a_call_by_reference);

		// init language support
		global $lng;
	}

} // END class.LanguageFolderObject
?>
