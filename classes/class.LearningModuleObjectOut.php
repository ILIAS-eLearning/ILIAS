<?php
/**
* Class LearningModuleObjectOut
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de> 
* $Id$Id: class.LearningModuleObjectOut.php,v 1.4 2002/12/12 15:33:25 shofmann Exp $
* 
* @extends ObjectOut
* @package ilias-core
*/

require_once("classes/class.ObjectOut.php");

class LearningModuleObjectOut extends ObjectOut
{
	/**
	* Constructor
	* 
	* @access public
	*/
	function LearningModuleObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
	}
	
	/**
	* display dialogue for importing XML-LeaningObjects
	*
	*  @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=upload&type=".$_GET["type"].
						  "&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("upload"));
	}
	
	/**
	* display status information or report errors messages
	* in case of error
	* 
	* @access	public
	*/
	function uploadObject()
	{
		header("Location: adm_object.php?cmd=view&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"].
			   "&message=".urlencode($this->data["msg"]));
		exit();
		
		//nada para mirar ahora :-)
	}
} // END class.LeraningObject
?>