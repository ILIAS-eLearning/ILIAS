<?php
/**
 * Class Admin
 * Core functions for Role Based Access Control
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
class Admin 
{
	var $ilias;

	// constructor
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
			$_SESSION["Error_Message"] = "No permission to delete Object";
		}
		
		$clipboard = array( "node"		=> $_GET["obj_id"],
							"parent"	=> $_GET["parent"],
							"obj_list"	=> $_POST["id"],
							"cmd"		=> $_POST["cmd"]
						   );
								   
		$_SESSION["clipboard"] = $clipboard;
			
	}
		
	// create an new reference of an object in tree
	function copyObject()
	{
		global $clipboard;

		if (!isset($_POST["id"]))
		{
			$_SESSION["Error_Message"] = "No permission to delete Object";
		}
		
		$clipboard = array( "node"		=> $_GET["obj_id"],
							"parent"	=> $_GET["parent"],
							"obj_list"	=> $_POST["id"],
							"cmd"		=> $_POST["cmd"]
						   );
								   
		$_SESSION["clipboard"] = $clipboard;
	}
	
	// paste an object to new location in tree
	function pasteObject()
	{
		global $clipboard, $tree;
		
		if ($clipboard["cmd"] == "copy")
		{
			//$tree->copyNode();
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

		if (!isset($_POST["id"]))
		{
			$_SESSION["Error_Message"] = "No checkbox checked. Nothing happened. :-)";
		}
		else
		{		
			$rbacadmin = new RbacAdminH($this->ilias->db);
			$rbacsystem = new RbacSystemH($this->ilias->db);

			foreach($_POST["id"] as $id)
			{
				$tree->deleteTree($id);
				$rbacadmin->revokePermission($id);

				// CHECK ACCESS	
				if($rbacsystem->checkAccess('delete',$id,$_GET["obj_id"]))
				{
					$tree->deleteTree($id);
					$rbacadmin->revokePermission($id);
				}
				else
				{
					$_SESSION["Error_Message"] = "No permission to delete Object";
				}
			}
		}
	}
	
} // end class.Admin.php
?>