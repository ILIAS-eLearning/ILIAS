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


// PUBLIC METHODEN
	function Admin(&$a_ilias)
	{
		$this->ilias = $a_ilias;
	}
	function cutObject()
	{
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
	function copyObject()
	{
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
	function pasteObject()
	{
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
	function deleteObject()
	{
		global $tree;

		$rbacadmin = new RbacAdminH($this->ilias->db);
		foreach($_POST["id"] as $id)
		{
			$tree->deleteTree($id);
			$rbacadmin->revokePermission($id);
		}
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
}
?>