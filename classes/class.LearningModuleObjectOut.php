<?php
/**
* Class LearningModuleObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.LearningModuleObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
* 
* @extends ObjectOut
* @package ilias-core
*/

require_once("classes/class.ObjectOut.php");

class LearningModuleObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function LearningModuleObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
	}
	
	function importObject()
	{
		$this->getTemplateFile("import");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=upload&type=".$_GET["type"].
						  "&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("import"));
	}
	
	function uploadObject()
	{
		//nada para mirar
	}

} // END class.LeraningObject
?>