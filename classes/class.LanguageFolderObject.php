<?php
/**
* Class LanguageFolderObject
* 
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class LanguageFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function LanguageFolderObject()
	{
		$this->Object();
	}
	
	function viewObject()
	{
		global $lng, $tpl;
		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "type", "name", "status", "last_change");
		
		$langs = $lng->getAvailableLanguages();
		
		
		for ($i=0; $i<count($langs); $i++)
		{
		
			$val = $langs[$i];
			//visible data part
			$this->objectList["data"][] = array(
				"type" => "<img src=\"".$tpl->tplPath."/images/icon_lng_b.gif\" border=\"0\">",
				"name" => $val["name"],
				"status" => $val["status"],
				"last_change" => $val["lastchange"]
			);

			//control information
			$this->objectList["ctrl"][] = array(
				"type" => $val["type"],
				"obj_id" => $val["id"],
				"parent" => $val["parent"],
				"parent_parent" => $val["parent_parent"],
			);

		} //for
		return $this->objectList;
		
	} //function
	

	function getSubObjects()	
	{
		return false;
	} //function
	
} // END class.LanguageFolderObject
?>