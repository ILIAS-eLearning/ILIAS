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
		global $ilUser;
		
		if($a_user_id === null)
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
	
	function listObjects()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl, $objDefinition;
		
		
		$objects = ilObject::getAllOwnedRepositoryObjects($this->user_id);
		
		if(sizeof($objects))
		{
			include_once "Services/Form/classes/class.ilSelectInputGUI.php";
			$sel = new ilSelectInputGUI($lng->txt("type"), "type");
			$ilToolbar->addInputItem($sel, true);
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "listObjects"));
			$ilToolbar->addFormButton($lng->txt("ok"), "listObjects");

			$options = array();
			foreach(array_keys($objects) as $type)
			{			
				// #11050
				if(!$objDefinition->isPlugin($type))
				{				
					$options[$type] = $lng->txt("obj_".$type);								
				}
				else
				{					
					include_once("./Services/Component/classes/class.ilPlugin.php");
					$options[$type] = ilPlugin::lookupTxt("rep_robj", $type, "obj_".$type);
				}
			}		
			asort($options);
			$sel->setOptions($options);		

			$sel_type = (string)$_REQUEST["type"];		
			if($sel_type)
			{
				$sel->setValue($sel_type);
			}
			else
			{
				$sel_type = array_keys($options);
				$sel_type = array_shift($sel_type);
			}			
			$ilCtrl->setParameter($this, "type", $sel_type);
		}
		
		include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
		$tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id, $objects[$sel_type]);				
		$tpl->setContent($tbl->getHTML());	
	}
	
	function applyFilter()
	{		
		include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
		$tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id);
		$tbl->resetOffset();
		$tbl->writeFilterToSession();
		$this->listObjects();
	}
	
	function resetFilter()
	{
		include_once "Services/Object/classes/class.ilObjectOwnershipManagementTableGUI.php";
		$tbl = new ilObjectOwnershipManagementTableGUI($this, "listObjects", $this->user_id);
		$tbl->resetOffset();
		$tbl->resetFilter();
		$this->listObjects();
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
		$path = array("ilRepositoryGUI", $gui_class, $a_class);
		
		// #10495 - check if object type supports ilexportgui "directly"
		if($a_class == "ilExportGUI")
		{							
			try
			{
				$ilCtrl->getLinkTargetByClass($path);
			}
			catch(Exception $e)
			{
				switch($node["type"])
				{
					case "glo":
						$cmd = "exportList";
						$path = array("ilRepositoryGUI", "ilGlossaryEditorGUI", $gui_class);
						break;

					default:
						$cmd = "export";
						$path = array("ilRepositoryGUI", $gui_class);
						break;
				}						
				$ilCtrl->setParameterByClass($gui_class, "ref_id", $a_ref_id);	
				$ilCtrl->setParameterByClass($gui_class, "cmd", $cmd);
				$ilCtrl->redirectByClass($path);	
			}
		}
						
		$ilCtrl->setParameterByClass($a_class, "ref_id", $a_ref_id);	
		$ilCtrl->setParameterByClass($a_class, "cmd", $a_cmd);
		$ilCtrl->redirectByClass($path);				
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