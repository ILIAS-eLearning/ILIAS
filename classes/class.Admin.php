<?php
/**
* Class Admin
* Objectmanagement functions
* 
* @author Stefan Meyer <smeyer@databay.de>
* @author SAscha Hofamnn <shofmann@databay.de> 
* @version $Id$
* 
* @package ilias-core
*/
class Admin 
{
	/**
	* ilias object
	* @var object ilias
	* @access private
	*/
	var $ilias;

	/**
	* Constructor
	* @access public
	*/
	function Admin()
	{
		global $ilias;
		
		$this->ilias = $ilias;
	}

	/**
	* cut an object out from tree an copy information to clipboard
	* @access public
	*/
	function cutObject()
	{
		global $clipboard;
		
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
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

	/**
	* create an new reference of an object in tree
	* @access public
	*/	
	function copyObject()
	{
		global $clipboard;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
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

	/**
	* paste an object to new location in tree
	* @access public
	*/	
	function pasteObject()
	{
		global $clipboard, $tree, $rbacsystem, $rbacadmin, $rbacreview;
		
		switch ($clipboard["cmd"])
		{
			case "copy":
				$perform_paste = true;
				
				// check if pasting is permitted
				foreach ($clipboard["obj_list"] as $obj_id => $obj_type)
				{
					// get complete node_data of node
					$node_data = $tree->getNodeData($obj_id,$_GET["obj_id"]);
					// get subtree of node
					$subtree = $tree->getSubTree($node_data);
					// node & subtree of that node is saved here
					$all_subtree[] = $subtree;
					// remove node from subtree list
					$node_data = array_shift($subtree);
					// all node_data of each node is saved into this place
					$all_node_data[] = $node_data;

					if (!$rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$obj_type))
					{
						// ids of objects with no create permission
						$not_pastable[] = $obj_id;
						$perform_paste = false;
					}
				
					// now do the same for the subtree of that node
					if ($subtree)
					{
						foreach ($subtree as $subnode_data)
						{
							if (!$rbacsystem->checkAccess("create",$subnode_data["obj_id"],$subnode_data["parent"],$subnode_data["type"]))
							{
								// ids of objects which contain objects with no create permission 
								$not_empty[] = $node_data["obj_id"];
								$perform_paste = false;
								break;
							}
						}
					}
				}

				// throw error message
				if ($not_pastable)
				{
					$not_pastable = implode(",",$not_pastable);
					$this->ilias->raiseError("You have no permission to copy object(s) No. ".$not_deletable." io this place.<br />Action aborted",$this->ilias->error_obj->MESSAGE);
				}

				if ($not_empty)
				{
					$not_empty = implode(",",$not_empty);
					$this->ilias->raiseError("Following objects contain objects with no permission to create: ".$not_empty."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
				}

				// conduct pasting	
				if ($perform_paste)
				{
					foreach ($all_subtree as $subtree)
					{
						foreach ($subtree as $node_data)
						{
							// remove data from tbl.rbac_pa
							$rbacadmin->revokePermission($node_data["obj_id"],$node_data["parent"]);
	
							// remove data from tbl.rbac_fa & tbl.rbac_templates
							if ($node_data["type"] == "rolf")
							{
								// remove rolefolder from system
								deleteObject($node_data["obj_id"]);
								
								// fetch all roles assigned to this role folder
								$query = "SELECT * FROM rbac_fa ".
										 "WHERE parent = '".$node_data["obj_id"]."' ".
										 "AND parent_obj = '".$node_data["parent"]."'";
								$res = $this->ilias->db->query($query);
								
								$data = array();
	
								while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
								{
									$data[] = array (
													"rol_id"	 => $row->rol_id,
													"parent"	 => $row->parent,
													"parent_obj" => $row->parent_obj,
													"assign"	 => $row->assign
													);
								}
								
								// remove all local roles from system
								foreach ($data as $role)
								{							
									$rbacadmin->deleteLocalRole($role["rol_id"],$role["parent"]);
									
									deleteObject($role["rol_id"]);
								}
							}
						}
					}

					// Eintragen des Objektes in Tree
					$tree->insertNode($obj_id,$_GET["obj_id"]);
			
					// Suche aller Parent Rollen im Baum mit der Private-Methode getParentRoleIds()
					$parentRoles = $rbacadmin->getParentRoleIds();
			
					foreach ($parentRoles as $parRol)
					{
						// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
						$ops = $rbacreview->getOperations($parRol["obj_id"],$obj_type,$parRol["parent"]);
						$rbacadmin->grantPermission($parRol["obj_id"],$ops,$obj_id,$_GET["obj_id"]);
					}
				}
				break;

			case "cut":
				foreach ($clipboard["obj_list"] as $obj_id => $obj_type)
				{
					if ($rbacsystem->checkAccess("create",$_GET["obj_id"],$_GET["parent"],$obj_type))
					{
						// Eintragen des Objektes in Tree
						$tree->insertNode($obj_id,$_GET["obj_id"]);
		
						// Suche aller Parent Rollen im Baum mit der Private-Methode getParentRoleIds()
						$parentRoles = $rbacadmin->getParentRoleIds();
	
						foreach ($parentRoles as $parRol)
						{
							// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
							$ops = $rbacreview->getOperations($parRol["obj_id"],$obj_type,$parRol["parent"]);
							$rbacadmin->grantPermission($parRol["obj_id"],$ops,$obj_id,$_GET["obj_id"]);
						}
					}
					else
					{
						$this->ilias->raiseError("No permission to create object",$this->ilias->error_obj->MESSAGE);
					}
				}
//				$tree->moveNode($clipboard["obj_list"][0],$clipboard["parent"],$_GET["parent"]);

				$_SESSION["clipboard"] = "";
				session_unregister("clipboard");
				break;
		}
	}

	/**
	* delete objects from ILIAS
	* However objects are only removed from rbac system and tree!! That means that the objects
	* itself stay in the database but are not linked in any context within the system.
	* TODO: Trash Bin Feature (Functions to manage 'deleted' objects & remove them entirely from system.)
	* @access public
	*/
	function deleteObject()
	{
		global $tree, $rbacsystem, $rbacadmin;
		
		$perform_delete = true;

		// at least one object has to be chosen. 
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}
		else
		{		
			// first check if chosen object(s) are deletable by current user
			foreach ($_POST["id"] as $id)
			{
				// get complete node_data of node
				$node_data = $tree->getNodeData($id,$_GET["obj_id"]);
				// get subtree of node
				$subtree = $tree->getSubTree($node_data);
				// node & subtree of that node is saved here
				$all_subtree[] = $subtree;
				// remove node from subtree list
				$node_data = array_shift($subtree);
				
				// all node_data of each node is saved into this place
				$all_node_data[] = $node_data;

				// check delete permission of each node
				if (!$rbacsystem->checkAccess("delete",$node_data["obj_id"],$node_data["parent"]))
				{
					// ids of objects with no delete permission
					$not_deletable[] = $id;
					$perform_delete = false;
				}
				
				// now do the same for the subtree of that node
				if ($subtree)
				{
					foreach ($subtree as $subnode_data)
					{
						if (!$rbacsystem->checkAccess("delete",$subnode_data["obj_id"],$subnode_data["parent"]))
						{
							// ids of objects which contain objects with no delete permission 
							$not_empty[] = $node_data["obj_id"];
							$perform_delete = false;
							break;
						}
					}
				}
			}
			
			if ($not_deletable)
			{
				$not_deletable = implode(",",$not_deletable);
				$this->ilias->raiseError("You have no permission to delete object(s) No. ".$not_deletable."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
			}

			if ($not_empty)
			{
				$not_empty = implode(",",$not_empty);
				$this->ilias->raiseError("Following objects contain objects with no permission to delete: ".$not_empty."<br />Action aborted",$this->ilias->error_obj->MESSAGE);
			}
			
			// all chosen nodes & their subnodes are deletable => perform deletion 
			if ($perform_delete)
			{
				// remove data from tbl.tree
				foreach ($all_node_data as $node_data)
				{
					$tree->deleteTree($node_data);
				}
				
				foreach ($all_subtree as $subtree)
				{
					foreach ($subtree as $node_data)
					{
						// remove data from tbl.rbac_pa
						$rbacadmin->revokePermission($node_data["obj_id"],$node_data["parent"]);

						// remove data from tbl.rbac_fa & tbl.rbac_templates
						if ($node_data["type"] == "rolf")
						{
							// remove rolefolder from system
							deleteObject($node_data["obj_id"]);
							
							// fetch all roles assigned to this role folder
							$query = "SELECT * FROM rbac_fa ".
									 "WHERE parent = '".$node_data["obj_id"]."' ".
									 "AND parent_obj = '".$node_data["parent"]."'";
							$res = $this->ilias->db->query($query);
							
							$data = array();

							while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
							{
								$data[] = array (
												"rol_id"	 => $row->rol_id,
												"parent"	 => $row->parent,
												"parent_obj" => $row->parent_obj,
												"assign"	 => $row->assign
												);
							}
							
							// remove all local roles from system
							foreach ($data as $role)
							{							
								$rbacadmin->deleteLocalRole($role["rol_id"],$role["parent"]);
								
								deleteObject($role["rol_id"]);
							}
						}
					}
				}
				
				$this->ilias->raiseError("Object(s) deleted!",$this->ilias->error_obj->MESSAGE);
			}
		}
	}

	/**
	* remove clipboard from session
	* @access public
	*/	
	function clearObject()
	{
		$_SESSION["clipboard"] = "";
		session_unregister("clipboard");	
	}
} // END class.Admin
?>