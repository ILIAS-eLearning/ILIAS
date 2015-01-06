<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Export User Interface Class
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @ingroup	ServicesExport
*
* @ilCtrl_Calls ilExportGUI:
*/
class ilExportGUI
{
	protected $formats = array();
	protected $custom_columns = array();
	protected $custom_multi_commands = array();
	
	private $parent_gui = null;
	
	/**
	 * Constuctor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_parent_gui, $a_main_obj = null)
	{
		global $lng,$tpl;
		
		$this->parent_gui = $a_parent_gui;
		if ($a_main_obj == null)
		{
			$this->obj = $a_parent_gui->object;
		}
		else
		{
			$this->obj = $a_main_obj;
		}
		$lng->loadLanguageModule("exp");
		$this->tpl = $tpl;
	}

	/**
	 * @return ilExportTableGUI
	 */
	protected function buildExportTableGUI()
	{
		include_once("./Services/Export/classes/class.ilExportTableGUI.php");
		$table = new ilExportTableGUI($this, "listExportFiles", $this->obj);
		return $table;
	}

	/**
	 * get parent gui
	 * @return 
	 */
	protected function getParentGUI()
	{
		return $this->parent_gui;
	}
	
	/**
	 * Add formats
	 *
	 * @param	array	formats
	 */
	function addFormat($a_key, $a_txt = "", $a_call_obj = null, $a_call_func = "")
	{
		global $lng;
		
		if ($a_txt == "")
		{
			$a_txt = $lng->txt("exp_".$a_key);
		}
		$this->formats[] = array("key" => $a_key, "txt" => $a_txt,
			"call_obj" => $a_call_obj, "call_func" => $a_call_func);
	}
	
	/**
	 * Get formats
	 *
	 * @return	array	formats
	 */
	function getFormats()
	{
		return $this->formats;
	}
	
	/**
	 * Add custom column
	 *
	 * @param
	 * @return
	 */
	function addCustomColumn($a_txt, $a_obj, $a_func)
	{
		$this->custom_columns[] = array("txt" => $a_txt,
										"obj" => $a_obj,
										"func" => $a_func);
	}
	
	/**
	 * Add custom multi command
	 *
	 * @param
	 * @return
	 */
	function addCustomMultiCommand($a_txt, $a_obj, $a_func)
	{
		$this->custom_multi_commands[] = array("txt" => $a_txt,
										"obj" => $a_obj,
										"func" => $a_func);
	}
	
	/**
	 * Get custom multi commands
	 */
	function getCustomMultiCommands()
	{
		return $this->custom_multi_commands;
	}

	/**
	 * Get custom columns
	 *
	 * @param
	 * @return
	 */
	function getCustomColumns()
	{
		return $this->custom_columns;
	}

	/**
	 * Execute command
	 *
	 * @param
	 * @return
	 */
	function executeCommand()
	{
		global $ilCtrl, $ilAccess, $ilErr, $lng;
		
		// this should work (at least) for repository objects 
		if(method_exists($this->obj, 'getRefId') and $this->obj->getRefId())
		{		
			if(!$ilAccess->checkAccess('write','',$this->obj->getRefId()))
			{
				$ilErr->raiseError($lng->txt('permission_denied'),$ilErr->WARNING);
			}
		}
			
		$cmd = $ilCtrl->getCmd("listExportFiles");
		
		switch ($cmd)
		{
			case "listExportFiles":
				$this->$cmd();
				break;
				
			default:
				if (substr($cmd, 0, 7) == "create_")
				{
					$this->createExportFile();
				}
				else if (substr($cmd, 0, 6) == "multi_")	// custom multi command
				{
					$this->handleCustomMultiCommand();
				}
				else
				{
					$this->$cmd();
				}
				break;
		}
	}
	
	/**
	 * List export files
	 *
	 * @param
	 * @return
	 */
	function listExportFiles()
	{
		global $tpl, $ilToolbar, $ilCtrl, $lng;

		include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
		$button = ilSubmitButton::getInstance();		
		
		// creation buttons
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		if (count($this->getFormats()) > 1)
		{
			// type selection
			foreach ($this->getFormats() as $f)
			{
				$options[$f["key"]] = $f["txt"];
			}
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($lng->txt("type"), "format");
			$si->setOptions($options);
			$ilToolbar->addInputItem($si, true);
			
			$button->setCaption("exp_create_file");
			$button->setCommand("createExportFile");			
		}
		else
		{
			$format = $this->getFormats();
			$format = $format[0];
			
			$button->setCaption($lng->txt("exp_create_file")." (".$format["txt"].")", false);
			$button->setCommand("create_".$format["key"]);				
		}
		
		$ilToolbar->addButtonInstance($button);

		$table = $this->buildExportTableGUI();
		$table->setSelectAllCheckbox("file");
		foreach ($this->getCustomColumns() as $c)
		{
			$table->addCustomColumn($c["txt"], $c["obj"], $c["func"]); 
		}
		foreach ($this->getCustomMultiCommands() as $c)
		{
			$table->addCustomMultiCommand($c["txt"], "multi_".$c["func"]); 
		}
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Create export file
	 *
	 * @param
	 * @return
	 */
	function createExportFile()
	{
		global $ilCtrl, $lng;
		
		if ($ilCtrl->getCmd() == "createExportFile")
		{
			$format = ilUtil::stripSlashes($_POST["format"]);
		}
		else
		{
			$format = substr($ilCtrl->getCmd(), 7);
		}
		foreach ($this->getFormats() as $f)
		{
			if ($f["key"] == $format)
			{
				if (is_object($f["call_obj"]))
				{
					$f["call_obj"]->$f["call_func"]();
				}
				elseif($this->getParentGUI() instanceof ilContainerGUI)
				{
					return $this->showItemSelection();
				}
				else if ($format == "xml")		// standard procedure
				{
					include_once("./Services/Export/classes/class.ilExport.php");
					$exp = new ilExport();
					$exp->exportObject($this->obj->getType(),$this->obj->getId(), "5.0.0");
				}
			}
		}
		
		ilUtil::sendSuccess($lng->txt("exp_file_created"), true);
		$ilCtrl->redirect($this, "listExportFiles");
	}
	
	/**
	 * Confirm file deletion
	 */
	function confirmDeletion()
	{
		global $ilCtrl, $tpl, $lng;
			
		if (!is_array($_POST["file"]) || count($_POST["file"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listExportFiles");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("exp_really_delete"));
			$cgui->setCancel($lng->txt("cancel"), "listExportFiles");
			$cgui->setConfirm($lng->txt("delete"), "delete");
			
			foreach ($_POST["file"] as $i)
			{
				if(strpos($i, ':') !== false)
				{
					$iarr     = explode(":", $i);
					$filename = $iarr[1];
				}
				else
				{
					$filename = $i;
				}
				$cgui->addItem("file[]", $i, $filename);
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Delete files
	 */
	function delete()
	{
		global $ilCtrl;
		
		foreach($_POST["file"] as $file)
		{
			$file = explode(":", $file);
			
			$file[1] = basename($file[1]);
			
			include_once("./Services/Export/classes/class.ilExport.php");
			$export_dir = ilExport::_getExportDirectory($this->obj->getId(),
				str_replace("..", "", $file[0]), $this->obj->getType());

			$exp_file = $export_dir."/".str_replace("..", "", $file[1]);
			$exp_dir = $export_dir."/".substr($file[1], 0, strlen($file[1]) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				ilUtil::delDir($exp_dir);
			}
			
			// delete entry in database
			include_once './Services/Export/classes/class.ilExportFileInfo.php';
			$info = new ilExportFileInfo($this->obj->getId(),$file[0],$file[1]);
			$info->delete();
		}
		$ilCtrl->redirect($this, "listExportFiles");
	}
	
	/**
	 * Download file
	 */
	public function download()
	{
		global $ilCtrl, $lng;
						
		if(!isset($_GET["file"]) || 
			is_array($_GET["file"]))
		{			
			$ilCtrl->redirect($this, "listExportFiles");
		}

		$file = explode(":", trim($_GET["file"]));
		include_once("./Services/Export/classes/class.ilExport.php");
		$export_dir = ilExport::_getExportDirectory($this->obj->getId(),
			str_replace("..", "", $file[0]), $this->obj->getType());
		
		$file[1] = basename($file[1]);
		
		ilUtil::deliverFile($export_dir."/".$file[1],
			$file[1]);
	}
	
	/**
	 * Handle custom multi command
	 *
	 * @param
	 * @return
	 */
	function handleCustomMultiCommand()
	{
		global $ilCtrl;

		$cmd = substr($ilCtrl->getCmd(), 6);
		foreach ($this->getCustomMultiCommands() as $c)
		{
			if ($c["func"] == $cmd)
			{
				$c["obj"]->$c["func"]($_POST["file"]);
			}
		}
	}
	
	/**
	 * Show container item selection table
	 * @return 
	 */
	protected function showItemSelection()
	{
		global $tpl;
		
		$tpl->addJavaScript('./Services/CopyWizard/js/ilContainer.js');
		$tpl->setVariable('BODY_ATTRIBUTES','onload="ilDisableChilds(\'cmd\');"');

		include_once './Services/Export/classes/class.ilExportSelectionTableGUI.php';
		$table = new ilExportSelectionTableGUI($this,'listExportFiles');
		$table->parseContainer($this->getParentGUI()->object->getRefId());
		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * Save selection of subitems
	 * @return 
	 */
	protected function saveItemSelection()
	{
		global $tree,$objDefinition, $ilAccess, $ilCtrl,$lng;

		include_once './Services/Export/classes/class.ilExportOptions.php';
		$eo = ilExportOptions::newInstance(ilExportOptions::allocateExportId());
		$eo->addOption(ilExportOptions::KEY_ROOT,0,0,$this->obj->getId());
		
		$items_selected = false;
		foreach($tree->getSubTree($root = $tree->getNodeData($this->getParentGUI()->object->getRefId())) as $node)
		{
			if($node['type'] == 'rolf')
			{
				continue;
			}
			if($node['ref_id'] == $this->getParentGUI()->object->getRefId())
			{
				$eo->addOption(
					ilExportOptions::KEY_ITEM_MODE,
					$node['ref_id'],
					$node['obj_id'],
					ilExportOptions::EXPORT_BUILD
				);
				continue;
			}
			// no export available or no access
			if(!$objDefinition->allowExport($node['type']) or !$ilAccess->checkAccess('write','',$node['ref_id']))
			{
			
				$eo->addOption(
					ilExportOptions::KEY_ITEM_MODE,
					$node['ref_id'],
					$node['obj_id'],
					ilExportOptions::EXPORT_OMIT
				);
				continue;
			}
			
			$mode = isset($_POST['cp_options'][$node['ref_id']]['type']) ? 
				$_POST['cp_options'][$node['ref_id']]['type'] : 
				ilExportOptions::EXPORT_OMIT;
			$eo->addOption(
				ilExportOptions::KEY_ITEM_MODE,
				$node['ref_id'],
				$node['obj_id'],
				$mode
			);
			if($mode != ilExportOptions::EXPORT_OMIT)
			{
				$items_selected = true;
			}
		}
		
		include_once("./Services/Export/classes/class.ilExport.php");
		if($items_selected)
		{
			// TODO: move this to background soap
			$eo->read();
			$exp = new ilExport();
			foreach($eo->getSubitemsForCreation($this->obj->getRefId()) as $ref_id)
			{
				$obj_id = ilObject::_lookupObjId($ref_id);
				$type = ilObject::_lookupType($obj_id);
				$exp->exportObject($type,$obj_id,'4.1.0');
			}
			// Fixme: there is a naming conflict between the container settings xml and the container subitem xml. 
			sleep(1);
			// Export container
			include_once './Services/Export/classes/class.ilExportContainer.php';
			$cexp = new ilExportContainer($eo);
			$cexp->exportObject($this->obj->getType(),$this->obj->getId(),'4.1.0');
		}
		else
		{
			$exp = new ilExport();
			$exp->exportObject($this->obj->getType(),$this->obj->getId(), "4.1.0");
		}

		// Delete export options
		$eo->delete();

		ilUtil::sendSuccess($lng->txt('export_created'),true);
		$ilCtrl->redirect($this, "listExportFiles");
	}
}
?>