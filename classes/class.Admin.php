<?php
/**
 * Class Admin
 * Core functions for Role Based Access Control
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
*/
class Admin 
{
	var $ilias;

	/**
	* constructor
	* @param object ILIAS
	*/
	function Admin(&$a_ilias)
	{
		$this->ilias = $a_ilias;
	}


// PUBLIC METHODEN
	
	// cut an object out from tree an copy information to clipboard
	function cutObject()
	{
		global $clipboard;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_class->MESSAGE);
		}
		
		// fetch object type for each obj_id
		foreach ($_POST["id"] as $val)
		{
			$obj = getObject($val);
			$obj_list[$val] = $obj["type"];
		}

		// destroy $obj
		unset($obj);		

		// write all nessessary data into clipboard
		$clipboard = array( "node"		=> $_GET["obj_id"],
							"parent"	=> $_GET["parent"],
							"obj_list"	=> $obj_list,
							"cmd"		=> $_POST["cmd"]
						   );
								   
		// save clipboard to session
		$_SESSION["clipboard"] = $clipboard;
	}
		
	// create an new reference of an object in tree
	function copyObject()
	{
		global $clipboard;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_class->MESSAGE);
		}
		
		// fetch object type for each obj_id
		foreach ($_POST["id"] as $val)
		{
			$obj = getObject($val);
			$obj_list[$val] = $obj["type"];
		}
		
		// destroy $obj
		unset($obj);		

		// write all nessessary data into clipboard
		$clipboard = array( "node"		=> $_GET["obj_id"],
							"parent"	=> $_GET["parent"],
							"obj_list"	=> $obj_list,
							"cmd"		=> $_POST["cmd"]
						   );
								   
	
		// save clipboard to session
		$_SESSION["clipboard"] = $clipboard;
	}
	
	// paste an object to new location in tree
	function pasteObject()
	{
		global $clipboard, $tree;
		
		if ($clipboard["cmd"] == "copy")
		{
			$rbacsystem = new RbacSystemH($this->ilias->db);
			//$tree = new Tree($_GET["obj_id"],$_GET["parent"]);
			
			foreach ($clipboard["obj_list"] as $obj_id => $obj_type)
			{
				if ($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$obj_type))
				{
					$rbacreview = new RbacReviewH($this->ilias->db);
					$rbacadmin = new RbacAdminH($this->ilias->db); 

					// Eintragen des Objektes in Tree
					$tree->insertNode($obj_id,$_GET["obj_id"]);
	
					// Suche aller Parent Rollen im Baum mit der Private-Methode getParentRoleIds()
					$parentRoles = $rbacadmin->getParentRoleIds();

					foreach($parentRoles as $parRol)
					{
						// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
						$ops = $rbacreview->getOperations($parRol["obj_id"],$obj_type,$parRol["parent"]);
						$rbacadmin->grantPermission($parRol["obj_id"],$ops,$obj_id,$_GET["obj_id"]);
					}
				}
				else
				{
					$this->ilias->raiseError("No permission to create object",$this->ilias->error_class->MESSAGE);
			
				}
			}
		}		
	
		if ($clipboard["cmd"] == "cut")
		{
			echo $clipboard["obj_list"][0];
			$tree->moveNode($clipboard["obj_list"][0],$clipboard["parent"],$_GET["parent"]);
			$_SESSION["clipboard"] = "";
			session_unregister("clipboard");
		}
	}
	
	// delete an object from tree
	function deleteObject()
	{
		global $tree;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_class->MESSAGE);
		}

		else
		{		
			$rbacadmin = new RbacAdminH($this->ilias->db);
			$rbacsystem = new RbacSystemH($this->ilias->db);

			foreach($_POST["id"] as $id)
			{

				// CHECK ACCESS	
				if($rbacsystem->checkAccess("delete",$id,$_GET["obj_id"]))
				{
					$tree->deleteTree($id);
					$rbacadmin->revokePermission($id);
				}
				else
				{
					$this->ilias->raiseError("No permission to delete object",$this->ilias->error_class->MESSAGE);
				}
			}
		}
	}
	
} // end class.Admin.php
?>