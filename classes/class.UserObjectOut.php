<?php
/**
* Class UserObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.UserObjectOut.php,v 1.5 2003/02/11 14:43:22 akill Exp $
* 
* @extends Object
* @package ilias-core
*/

class UserObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access	public
	*/
	function UserObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "usr";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
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

		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"]."&new_type=".$_POST["new_type"]);
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
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=update"."&ref_id=".$_GET["ref_id"]);
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
		$this->tpl->setVariable("ACTIVE_ROLE_FORMACTION","adm_object.php?cmd=activeRoleSave&ref_id=".$_GET["ref_id"]);
		$this->tpl->parseCurrentBlock();
		// END ACTIVE ROLES

		if($this->data["active_role"]["access"] == true)
		{
		   $this->tpl->touchBlock("TABLE_SUBMIT");
	    }
	}

	function activeRoleSaveObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=edit");
		exit;
	}	   

	function updateObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["parent"]."&parent=".
			   $_GET["parent_parent"]."&cmd=view");
		exit;
	}
} // END class.UserObjectOut
?>
