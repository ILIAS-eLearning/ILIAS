<?php
/**
* Class UserObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.UserObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/

class UserObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function UserObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
	}
	
	function createObject()
	{
		$this->getTemplateFile("edit","usr");
		foreach ($this->data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&obj_id=".$_GET["obj_id"].
						  "&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&new_type=".$_POST["new_type"]);
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	function editObject()
	{
		$this->getTemplateFile("edit","usr");
		foreach ($this->data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=update"."&obj_id=".$_GET["obj_id"].
						  "&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	function updateObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["parent"]."&parent=".
			   $_GET["parent_parent"]."&cmd=view");
		exit();
	}

} // END class.UserObjectOut
?>