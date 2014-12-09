<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Repository GUI Utilities
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
class ilRepUtilGUI
{

	/**
	* Constructor
	*
	* @param	object		parent gui object
	* @param	string		current parent command (like in table2gui)
	*/
	function __construct($a_parent_gui, $a_parent_cmd = "")
	{
		$this->parent_gui = $a_parent_gui;
		$this->parent_cmd = $a_parent_cmd;
	}
	
	
	/**
	* Show delete confirmation table
	*/
	function showDeleteConfirmation($a_ids, $a_supress_message = false)
	{
		global $lng, $ilSetting, $ilCtrl, $tpl, $objDefinition;

		if (!is_array($a_ids) || count($a_ids) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			return false;
		}
		
		// Remove duplicate entries
		$a_ids = array_unique((array) $a_ids);

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();

		if(!$a_supress_message)
		{
			$msg = $lng->txt("info_delete_sure");
			
			if (!$ilSetting->get('enable_trash'))
			{
				$msg .= "<br/>".$lng->txt("info_delete_warning_no_trash");
			}
			
			$cgui->setHeaderText($msg);
		}
		$cgui->setFormAction($ilCtrl->getFormAction($this->parent_gui));
		$cgui->setCancel($lng->txt("cancel"), "cancelDelete");
		$cgui->setConfirm($lng->txt("confirm"), "confirmedDelete");
		
		$form_name = "cgui_".md5(uniqid());
		$cgui->setFormName($form_name);

		$deps = array();
		foreach ($a_ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$type = ilObject::_lookupType($obj_id);
			$title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'),$obj_id);
			$alt = ($objDefinition->isPlugin($type))
				? $lng->txt("icon")." ".ilPlugin::lookupTxt("rep_robj", $type, "obj_".$type)
				: $lng->txt("icon")." ".$lng->txt("obj_".$type);
			
			$title .= $this->handleMultiReferences($obj_id, $ref_id, $form_name);		
			
			$cgui->addItem("id[]", $ref_id, $title,
				ilObject::_getIcon($obj_id, "small", $type),
				$alt);

			ilObject::collectDeletionDependencies($deps, $ref_id, $obj_id, $type);
		}
		$deps_html = "";

		if (is_array($deps) && count($deps) > 0)
		{
			include_once("./Services/Repository/classes/class.ilRepDependenciesTableGUI.php");
			$tab = new ilRepDependenciesTableGUI($deps);
			$deps_html = "<br/><br/>".$tab->getHTML();
		}
		
		$tpl->setContent($cgui->getHTML().$deps_html);
		return true;
	}
	
	/**
	 * Build subitem list for multiple references
	 * 
	 * @param int $a_obj_id
	 * @param int $a_ref_id
	 * @param string $a_form_name
	 * @return string 
	 */
	function handleMultiReferences($a_obj_id, $a_ref_id, $a_form_name)
	{			
		global $lng, $ilAccess, $tree;
								
		// process
	
		$all_refs = ilObject::_getAllReferences($a_obj_id);			
		if(sizeof($all_refs) > 1)
		{				
			$lng->loadLanguageModule("rep");	
			
			$may_delete_any = 0;
			$counter = 0;
			$items = array();
			foreach($all_refs as $mref_id)	
			{			
				// not the already selected reference, no refs from trash
				if($mref_id != $a_ref_id && !$tree->isDeleted($mref_id))
				{
					if($ilAccess->checkAccess("read", "", $mref_id))
					{																									
						$may_delete = false;
						if($ilAccess->checkAccess("delete", "", $mref_id))
						{
							$may_delete = true;	
							$may_delete_any++;
						}
												
						$items[] = array("id" => $mref_id,
							"path" => array_shift($this->buildPath(array($mref_id))),
							"delete" => $may_delete);
					}
					else
					{
						$counter++;
					}					
				}
			}

			
			// render

			$tpl = new ilTemplate("tpl.rep_multi_ref.html", true, true, "Services/Repository");

			$tpl->setVariable("TXT_INTRO", $lng->txt("rep_multiple_reference_deletion_intro"));
			
			if($may_delete_any)
			{
				$tpl->setVariable("TXT_INSTRUCTION", $lng->txt("rep_multiple_reference_deletion_instruction"));
			}
			
			if($items)
			{				
				$var_name = "mref_id[]";
				
				foreach($items as $item)
				{
					if($item["delete"])
					{
						$tpl->setCurrentBlock("cbox");
						$tpl->setVariable("ITEM_NAME", $var_name);
						$tpl->setVariable("ITEM_VALUE", $item["id"]);													
						$tpl->parseCurrentBlock();		
					}
					else
					{
						$tpl->setCurrentBlock("item_info");
						$tpl->setVariable("TXT_ITEM_INFO", $lng->txt("rep_no_permission_to_delete"));													
						$tpl->parseCurrentBlock();	
					}
					
					$tpl->setCurrentBlock("item");
					$tpl->setVariable("ITEM_TITLE", $item["path"]);													
					$tpl->parseCurrentBlock();					
				}
				
				if($may_delete_any > 1)
				{	
					$tpl->setCurrentBlock("cbox");
					$tpl->setVariable("ITEM_NAME", "sall_".$a_ref_id);
					$tpl->setVariable("ITEM_VALUE", "");			
					$tpl->setVariable("ITEM_ADD", " onclick=\"il.Util.setChecked('".
						$a_form_name."', '".$var_name."', document.".$a_form_name.
						".sall_".$a_ref_id.".checked)\"");
					$tpl->parseCurrentBlock();							
					
					$tpl->setCurrentBlock("item");
					$tpl->setVariable("ITEM_TITLE", $lng->txt("select_all"));													
					$tpl->parseCurrentBlock();		
				}
			}
			
			if($counter)
			{
				$tpl->setCurrentBlock("add_info");
				$tpl->setVariable("TXT_ADDITIONAL_INFO", 
					sprintf($lng->txt("rep_object_references_cannot_be_read"), $counter));
				$tpl->parseCurrentBlock();		
			}

			return $tpl->get();
		}				
	}
	
	/**
	* Get trashed objects for a container
	*
	* @param	interger	ref id of container
	*/
	function showTrashTable($a_ref_id)
	{
		global $tpl, $tree, $lng;
		
		$objects = $tree->getSavedNodeData($a_ref_id);
		
		if (count($objects) == 0)
		{
			ilUtil::sendInfo($lng->txt("msg_trash_empty"));
			return;
		}
		include_once("./Services/Repository/classes/class.ilTrashTableGUI.php");
		$ttab = new ilTrashTableGUI($this->parent_gui, "trash");
		$ttab->setData($objects);
		
		$tpl->setContent($ttab->getHTML());
	}
	
	/**
	* Restore objects from trash
	*
	* @param	integer		current ref id
	* @param	array		array of ref ids to be restored
	*/
	function restoreObjects($a_cur_ref_id, $a_ref_ids)
	{
		global $lng;
		
		if (!is_array($a_ref_ids) || count($a_ref_ids) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"),true);
			return false;
		}
		else
		{
			try
			{
				include_once("./Services/Repository/classes/class.ilRepUtil.php");
				ilRepUtil::restoreObjects($a_cur_ref_id, $a_ref_ids);
				ilUtil::sendSuccess($lng->txt("msg_undeleted"),true);
			}
			catch (Exception $e)
			{
				ilUtil::sendFailure($e->getMessage(),true);
				return false;
			}
		}
		return true;
	}
	
	/**
	* Delete objects
	*/
	function deleteObjects($a_cur_ref_id, $a_ref_ids)
	{
		global $ilSetting, $lng;
		
		if (!is_array($a_ref_ids) || count($a_ref_ids) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			return false;
		}
		else
		{
			include_once("./Services/Repository/classes/class.ilRepUtil.php");
			try
			{
				ilRepUtil::deleteObjects($a_cur_ref_id, $a_ref_ids);
				if ($ilSetting->get('enable_trash'))
				{
					ilUtil::sendSuccess($lng->txt("info_deleted"),true);
				}
				else
				{
					ilUtil::sendSuccess($lng->txt("msg_removed"),true);
				}
			}
			catch (Exception $e)
			{
				ilUtil::sendFailure($e->getMessage(), true);
				return false;
			}
		}
	}
	
	/**
	* Remove objects from system
	*/
	function removeObjectsFromSystem($a_ref_ids, $a_from_recovery_folder = false)
	{
		global $lng;
		
		if (!is_array($a_ref_ids) || count($a_ref_ids) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			return false;
		}
		else
		{
			include_once("./Services/Repository/classes/class.ilRepUtil.php");
			try
			{
				ilRepUtil::removeObjectsFromSystem($a_ref_ids, $a_from_recovery_folder);
				ilUtil::sendSuccess($lng->txt("msg_removed"),true);
			}
			catch (Exception $e)
			{
				ilUtil::sendFailure($e->getMessage(), true);
				return false;
			}
		}

		return true;
	}
	
	/**
 	 * Build path with deep-link
	 *
	 * @param	array	$ref_ids
	 * @return	array 
	 */
	protected function buildPath($ref_ids)
	{
		global $tree;

		include_once 'Services/Link/classes/class.ilLink.php';
		
		if(!count($ref_ids))
		{
			return false;
		}
		
		$result = array();
		foreach($ref_ids as $ref_id)
		{
			$path = "";
			$path_full = $tree->getPathFull($ref_id);
			foreach($path_full as $idx => $data)
			{				
				if($idx)
				{
					$path .= " &raquo; ";
				}
				if($ref_id != $data['ref_id'])
				{
					$path .= $data['title'];
				}
				else
				{
					$path .= ('<a target="_top" href="'.
							  ilLink::_getLink($data['ref_id'],$data['type']).'">'.
							  $data['title'].'</a>');
				}
				
			}

			$result[] = $path;
		}
		return $result;
	}

	/**
	 * Confirmation for trash
	 *
	 * @param array $a_ids ref_ids
	 */
	public function confirmRemoveFromSystemObject($a_ids)
	{
		global $ilCtrl, $lng, $objDefinition, $tpl;
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		if(!is_array($a_ids))
		{
			$a_ids = array($a_ids);
		}

		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this->parent_gui));
		$cgui->setCancel($lng->txt("cancel"), "trash");
		$cgui->setConfirm($lng->txt("confirm"), "removeFromSystem");
		$cgui->setFormName("trash_confirmation");
		$cgui->setHeaderText($lng->txt("info_delete_sure"));

		foreach($a_ids as $id)
		{
			$obj_id = ilObject::_lookupObjId($id);
			$type = ilObject::_lookupType($obj_id);
			$title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'),$obj_id);
			$alt = ($objDefinition->isPlugin($type))
				? $lng->txt("icon")." ".ilPlugin::lookupTxt("rep_robj", $type, "obj_".$type)
				: $lng->txt("icon")." ".$lng->txt("obj_".$type);

			$cgui->addItem("trash_id[]", $id, $title,
				ilObject::_getIcon($obj_id, "small", $type),
				$alt);
		}

		$tpl->setContent($cgui->getHTML());
	}
}
?>