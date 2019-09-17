<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSAHSPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilSAHSPresentationGUI.php 11714 2006-07-30 17:15:55Z akill $
*
* @ilCtrl_Calls ilSAHSEditGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilObjSCORMLearningModuleGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilSAHSEditGUI: ilObjSCORM2004LearningModuleGUI, ilExportGUI, ilObjSAHSLearningModuleGUI, ilLTIProviderObjectSettingGUI
*
* @ingroup ModulesScormAicc
*/
class ilSAHSEditGUI
{
	var $ilias;
	var $tpl;
	var $lng;

	function __construct()
	{
		global $DIC;
		$ilias = $DIC['ilias'];
		$tpl = $DIC['tpl'];
		$lng = $DIC['lng'];
		$ilCtrl = $DIC['ilCtrl'];

		$this->ilias = $ilias;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		
		$this->ctrl->saveParameter($this, "ref_id");
	}
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		global $DIC;
		$lng = $DIC['lng'];
		$ilAccess = $DIC['ilAccess'];
		$ilNavigationHistory = $DIC['ilNavigationHistory'];
		$ilias = $DIC['ilias'];
		$ilCtrl = $DIC['ilCtrl'];
		$GLOBALS['DIC']["ilLog"]->write ("bc:".$_GET["baseClass"]."; nc:".$this->ctrl->getNextClass($this)."; cmd:".$this->ctrl->getCmd());

		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";

		$lng->loadLanguageModule("content");

		// permission
		if (!$ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		// add entry to navigation history
		$ilNavigationHistory->addItem($_GET["ref_id"],
			"ilias.php?baseClass=ilSAHSEditGUI&ref_id=".$_GET["ref_id"], "lm");

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
		$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

		switch($type)
		{
			
			case "scorm2004":
				include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModuleGUI.php");
				$this->slm_gui = new ilObjSCORM2004LearningModuleGUI("", $_GET["ref_id"],true,false);
				break;
				
			case "scorm":
				include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php");
				$this->slm_gui = new ilObjSCORMLearningModuleGUI("", $_GET["ref_id"],true,false);
				break;
		}

		if ($next_class == "")
		{
			switch($type)
			{
				
				case "scorm2004":
					$this->ctrl->setCmdClass("ilobjscorm2004learningmodulegui");
					break;
					
				case "scorm":
					$this->ctrl->setCmdClass("ilobjscormlearningmodulegui");
					break;
			}
			$next_class = $this->ctrl->getNextClass($this);
		}

		switch($next_class)
		{ 
		case "ilobjscormlearningmodulegui":
		case "ilobjscorm2004learningmodulegui":
			$ret = $this->ctrl->forwardCommand($this->slm_gui);
			break;

		case "ilexportgui":
			$obj_id = ilObject::_lookupObjectId($_GET["ref_id"]);
			if ($cmd == "create_xml")
			{
				require_once("Modules/ScormAicc/classes/class.ilScormAiccExporter.php");
				$exporter = new ilScormAiccExporter();
				$xml = $exporter->getXmlRepresentation("sahs", "5.1.0", ilObject::_lookupObjectId($_GET["ref_id"]));
			}
			else if ($cmd == "download")
			{
				$file = $_GET["file"];
				$ftmp = explode (":", $file);
				$fileName = $ftmp[1];
				require_once ("./Services/Export/classes/class.ilExport.php");
				$exportDir = ilExport::_getExportDirectory($obj_id);
				ilUtil::deliverFile ($exportDir . "/" . $fileName, $fileName);
			}
			else if ($cmd == "confirmDeletion")
			{
			}
			$this->ctrl->setCmd ("export");
			ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&cmd=export&ref_id=".$_GET["ref_id"]);
			break;


		default:
			die ("ilSAHSEdit: Class $next_class not found.");;
		}
		
		$this->tpl->show();
	}
}
?>
