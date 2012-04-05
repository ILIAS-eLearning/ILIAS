<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjectOwnershipManagementGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjectOwnershipManagementGUI:
*/
class ilObjectOwnershipManagementGUI 
{
	protected $user_id; // [int]
	
	function __construct($a_user_id = null)
	{
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		$this->user_id = (int)$a_user_id;
	}
	
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class =$ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{			
			default:									
				if(!$cmd)
				{
					$cmd = "listObjects";
				}
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	function listObjects($a_load_data = false)
	{
		global $tpl;
				
		include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
		$tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id, $a_load_data);
		
		if(!$a_load_data)
		{
			$tbl->disable("content");
			$tbl->disable("header");
		}
		
		$tpl->setContent($tbl->getHTML());	
	}
	
	function applyFilter()
	{		
		include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
		$tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id, false);
		$tbl->resetOffset();
		$tbl->writeFilterToSession();
		$this->listObjects(true);
	}
	
	function resetFilter()
	{
		include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
		$tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id, false);
		$tbl->resetOffset();
		$tbl->resetFilter();
		$this->listObjects(true);
	}
	
	protected function redirectParentCmd($a_ref_id, $a_cmd)
	{
		global $tree, $ilCtrl;
		
		$parent = $tree->getParentId($a_ref_id);		
		$ilCtrl->setParameterByClass("ilRepositoryGUI", "ref_id", $parent);
		$ilCtrl->setParameterByClass("ilRepositoryGUI", "item_ref_id", $a_ref_id);
		$ilCtrl->setParameterByClass("ilRepositoryGUI", "cmd", $a_cmd);
		$ilCtrl->redirectByClass("ilRepositoryGUI");		
	}
	
	protected function redirectCmd($a_ref_id, $a_class, $a_cmd = null)
	{
		global $ilCtrl, $tree, $objDefinition;
			
		$node = $tree->getNodeData($a_ref_id);
		$gui_class = "ilObj".$objDefinition->getClassName($node["type"])."GUI";
		
		$ilCtrl->setParameterByClass("ilRepositoryGUI", "ref_id", $a_ref_id);	
		$ilCtrl->setParameterByClass("ilRepositoryGUI", "cmd", $a_cmd);
		$ilCtrl->redirectByClass(array("ilRepositoryGUI", $gui_class, $a_class));	
	}
	
	function delete()
	{		
		$ref_id = (int)$_REQUEST["ownid"];
		$this->redirectParentCmd($ref_id, "delete");
	}
	
	function move()
	{
		$ref_id = (int)$_REQUEST["ownid"];
		$this->redirectParentCmd($ref_id, "cut");
	}
	
	function export()
	{
		$ref_id = (int)$_REQUEST["ownid"];
		$this->redirectCmd($ref_id, "ilExportGUI");				
	}
	
	function changeOwner()
	{
		$ref_id = (int)$_REQUEST["ownid"];
		$this->redirectCmd($ref_id, "ilPermissionGUI", "owner");			
	}	
}

?>