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
	*
	* @param	int		$a_id		object id
	* @access public
	*/
	function LanguageObject($a_id)
	{
		$this->Object($a_id);
	}

	
	function editObject($a_order, $a_direction)
	{
		global $rbacsystem, $rbacreview;

		if ($rbacsystem->checkAccess('write',$this->parent,$_GET["parent_parent"]) || $this->id == $_SESSION["AccountId"])
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
