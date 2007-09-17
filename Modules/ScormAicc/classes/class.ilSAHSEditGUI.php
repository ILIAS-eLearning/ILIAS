<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilSAHSPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilSAHSPresentationGUI.php 11714 2006-07-30 17:15:55Z akill $
*
* @ilCtrl_Calls ilSAHSEditGUI: ilObjSCORMLearningModuleGUI, ilObjAICCLearningModuleGUI, ilObjHACPLearningModuleGUI
* @ilCtrl_Calls ilSAHSEditGUI: ilObjSCORM2004LearningModuleGUI
*
* @ingroup ModulesScormAicc
*/
class ilSAHSEditGUI
{
	var $ilias;
	var $tpl;
	var $lng;

	function ilSAHSEditGUI()
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		
		$this->ctrl->saveParameter($this, "ref_id");
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess, $ilNavigationHistory;

		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";

		$lng->loadLanguageModule("content");

		// permission
		if (!$ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
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

			case "aicc":
				include_once("./Modules/ScormAicc/classes/class.ilObjAICCLearningModuleGUI.php");
				$this->slm_gui = new ilObjAICCLearningModuleGUI("", $_GET["ref_id"],true,false);
				break;
				
			case "hacp":
				include_once("./Modules/ScormAicc/classes/class.ilObjHACPLearningModuleGUI.php");
				$this->slm_gui = new ilObjHACPLearningModuleGUI("", $_GET["ref_id"],true,false);
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
	
				case "aicc":
					$this->ctrl->setCmdClass("ilobjaicclearningmodulegui");
					break;
					
				case "hacp":
					$this->ctrl->setCmdClass("ilobjhacplearningmodulegui");
					break;
			}
			$next_class = $this->ctrl->getNextClass($this);
		}

		switch($next_class)
		{
			case "ilobjscormlearningmodulegui":
			case "ilobjscorm2004learningmodulegui":
				$ret =& $this->ctrl->forwardCommand($this->slm_gui);
				break;

			case "ilobjaicclearningmodulegui":
				$ret =& $this->ctrl->forwardCommand($this->slm_gui);
				break;

			case "ilobjhacplearningmodulegui":
				$ret =& $this->ctrl->forwardCommand($this->slm_gui);
				break;

			default:
				die ("ilSAHSEdit: Class $next_class not found.");;
		}
		
		$this->tpl->show();
	}
}
?>
