<?php
/**
* Class UserObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.UserObjectOut.php,v 1.7 2003/03/12 16:52:25 akill Exp $
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
		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=update");
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


	/**
	* save user data
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin,$tree;
		
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$user = new User();
			$user->setData($_POST["Fobject"]);
			
			//create new UserObject
			$userObj = new UserObject();
			$userObj->setTitle($user->getFullname());
			$userObj->setDescription($user->getEmail());
			$userObj->create();

			$user->setId($userObj->getId());

			//insert user data in table user_data
			$user->saveAsNew();

			//set role entries
			$rbacadmin->assignUser($_POST["Fobject"]["default_role"],$user->getId(),true);
			
			//create new usersetting entry 
			$settingObj = new Object();
			$settingObj->setType("uset");
			$settingObj->setTitle($user->getFullname());
			$settingObj->setDescription("User Setting Folder");
			$settingObj->create();
			$settingObj->createReference();
			//$uset_id = createNewObject("uset",$user->getFullname(),"User Setting Folder");
			//$uset_ref = createNewReference($uset_id);
			
			//create usertree from class.user.php
			// tree_id is the obj_id of user not ref_id!
			// this could become a problem with same ids
			$tree->addTree($user->getId(), $settingObj->getRefId());
			
			//add notefolder to user tree
			$userTree = new tree(0,0,$user->getId());
			require_once ("classes/class.NoteFolderObject.php");
			$notfObj = new NoteFolderObject();
			$notfObj->setTitle($user->getFullname());
			$notfObj->setDescription("Note Folder Object");
			$notfObj->create();
			$notfObj->createReference();
			$userTree->insertNode($notfObj->getRefId(), $settingObj->getRefId());
		}
		else
		{
			$this->ilias->raiseError("No permission to write to user folder",$this->ilias->error_obj->WARNING);
		}
	}

	
	/**
	* update object in db
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacadmin;
		if ($rbacsystem->checkAccess("write", $this->object->getRefId())
			|| $this->object->getId() == $_SESSION["AccountId"])
		{
			$user = new User($this->object->getId());
			$user->setData($_POST["Fobject"]);
			$user->update();
			$this->object->setTitle($user->getFullname());
			$this->object->setDescription($user->getEmail());
			$this->update = $this->object->update();
			$rbacadmin->updateDefaultRole($_POST["Fobject"]["default_role"], $user->getId());
		}
		else
		{
			$this->ilias->raiseError("No permission to modify user",$this->ilias->error_obj->WARNING);
		}
		//echo "adm_object.php?ref_id=".$this->ref_id."&cmd=view";
		header("Location: adm_object.php?ref_id=".$this->ref_id."&cmd=view");
		exit();
	}


	function activeRoleSaveObject()
	{
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=edit");
		exit;
	}	   

} // END class.UserObjectOut
?>
