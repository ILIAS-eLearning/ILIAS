<?php
/**
* Class TypeDefinitionObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/
class TypeDefinitionObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function TypeDefinitionObject()
	{
		$this->Object();
	}

	function viewObject()
	{
		global $rbacadmin, $tpl;

		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "type", "title", "description", "status");

		$ops_valid = $rbacadmin->getOperationsOnType($this->id);
		
		if ($ops_arr = getOperationList('',$_GET["order"],$_GET["direction"]))
		{
			$options = array("e" => "enabled","d" => "disabled");
		
			foreach ($ops_arr as $key => $ops)
			{
				// BEGIN ROW
				if (in_array($ops["ops_id"],$ops_valid))
				{
					$ops_status = 'e';
				}
				else
				{
					$ops_status = 'd';
				}
				
				$obj = $ops["ops_id"];
				$ops_options = TUtil::formSelect($ops_status,"id[$obj]",$options);

				//visible data part
				$this->objectList["data"][] = array(
					"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_perm_b.gif\" border=\"0\">",
					"title" => $ops["operation"],
					"description" => $ops["desc"],
					"status" => $ops_options
				);

				//control information
				$this->objectList["ctrl"][] = array(
					"type" => "perm",
					"obj_id" => $ops["ops_id"],
					"parent" => $this->id,
					"parent_parent" => $this->parent
				);
			}
		}
		return $this->objectList;
		
	}
	
	
	function editObject()
	{
		global $rbacsystem, $rbacreview;

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]))
		{
			$data = array();

			$data["fields"] = array();
			$data["fields"]["login"] = $user->data["login"];
			$data["fields"]["passwd"] = "********";
			$data["fields"]["title"] = $user->data["title"];
			$data["fields"]["gender"] = $gender;
			$data["fields"]["firstname"] = $user->data["FirstName"];
			$data["fields"]["lastname"] = $user->data["SurName"];
			$data["fields"]["email"] = $user->data["Email"];
			$data["fields"]["default_role"] = $role;
			$data["title"] = $user->data["Title"];
			

			if ($_GET["obj_id"] == $_SESSION["AccountId"])
			{
				// BEGIN ACTIVE ROLE
				$assigned_roles = $rbacreview->assignedRoles($_GET["obj_id"]);
				
				foreach ($assigned_roles as $key => $role)
				{
					// BEGIN TABLE_ROLES
					$tpl->setCurrentBlock("TABLE_ROLES");
					$obj = getObject($role);
					$tpl->setVariable("CSS_ROW_ROLE",$key % 2 ? 'tblrow1' : 'tblrow2');
					$box = Tutil::formCheckBox(in_array($role,$_SESSION["RoleId"]),'active[]',$role);
					$tpl->setVariable("CHECK_ROLE",$box);
					$tpl->setVariable("ROLENAME",$obj["title"]);
					$tpl->parseCurrentBlock();
				}
				
				$tpl->setCurrentBlock("ACTIVE_ROLE");
				$tpl->setVariable("ACTIVE_ROLE_OBJ_ID",$_GET["obj_id"]);
				$tpl->setVariable("ACTIVE_ROLE_TPOS",$_GET["parent"]);
				$tpl->setVariable("ACTIVE_ROLE_PAR",$_GET["parent_parent"]);
				$tpl->parseCurrentBlock();
			}
			return $data;
		}
		else
		{
			$this->ilias->raiseError("No permission to edit user",$this->ilias->error_obj->WARNING);
		}
	}
	
	
} // END class.TypeDefinitionObject
?>