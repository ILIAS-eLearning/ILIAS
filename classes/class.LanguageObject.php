<?php
/**
* Class LanguageObject
* 
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @extends Object
* @package ilias-core
*/
class LanguageObject extends Object
{
	/**
	* separator of module, identifier & values
	* in language files
	*
	* @var		string
	* @access	private
	*/
	var $separator = "#:#";

	/**
	* Constructor
	* @access public
	*/
	function LanguageObject()
	{
		$this->Object();
	}
	
	function editObject()
	{
		global $rbacsystem, $rbacreview;

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]) || $_GET["obj_id"] == $_SESSION["AccountId"])
		{
			$data = array();
			$lng2 = new Language($this->id);

			$data["fields"] = array();
			$data["fields"]["name"] = $lng2->name;
			
			return $data;
		}
		else
		{
			$this->ilias->raiseError("No permission to edit language",$this->ilias->error_obj->WARNING);
		}
	}

} // END class.LanguageObject
?>