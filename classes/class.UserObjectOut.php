<?php
/**
* Class UserObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.UserObjectOut.php,v 1.2 2002/12/05 13:39:40 shofmann Exp $
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
		
		// BEGIN ACTIVE ROLES
		$this->tpl->setCurrentBlock("ACTIVE_ROLE");

		// BEGIN TABLE ROLES
		$this->tpl->setCurrentBlock("TABLE_ROLES");

		$counter = 0;
		foreach($this->data["active_role"] as $role_id => $role)
		{
		   ++$counter;
		   $this->tpl->setVariable("ACTIVE_ROLE_CSS_ROW",TUtil::switchColor($counter,"tblrow2","tblrow1"));
		   $this->tpl->setVariable("CHECK_ROLE",$role["checkbox"]);
		   $this->tpl->setVariable("ROLENAME",$role["title"]);
		   $this->tpl->parseCurrentBlock();
		}
		// END TABLE ROLES
		$this->tpl->setVariable("ACTIVE_ROLE_FORMACTION","adm_object.php?cmd=activeRoleSave&obj_id=".$_GET["obj_id"].
								"&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		$this->tpl->parseCurrentBlock();
		// END ACTIVE ROLES
	}
	function activeRoleSaveObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=edit");
		exit();
	}	   

	function updateObject()
	{
		header("Location: adm_object.php?obj_id=".$_GET["parent"]."&parent=".
			   $_GET["parent_parent"]."&cmd=view");
		exit();
	}

} // END class.UserObjectOut
?>