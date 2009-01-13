<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilObjSAHSLearningModuleListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesScormAicc
*/


include_once "classes/class.ilObjectListGUI.php";

class ilObjSAHSLearningModuleListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjSAHSLearningModuleListGUI()
	{
		$this->ilObjectListGUI();
	}

	/**
	* initialisation
	*
	* this method should be overwritten by derived classes
	*/
	function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = true;
		$this->info_screen_enabled = true;
		$this->type = "sahs";
		$this->gui_class_name = "ilobjsahslearningmodulegui";
		
		// general commands array
		include_once('./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php');
		$this->commands = ilObjSAHSLearningModuleAccess::_getCommands();
	}

	/**
	* Overwrite this method, if link target is not build by ctrl class
	* (e.g. "lm_presentation.php", "forum.php"). This is the case
	* for all links now, but bringing everything to ilCtrl should
	* be realised in the future.
	*
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{
		switch($a_cmd)
		{
			case "view":
				$cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->ref_id;
				break;

			case "edit":
				$cmd_link = "ilias.php?baseClass=ilSAHSEditGUI&amp;ref_id=".$this->ref_id;
				break;

			case "infoScreen":
				$cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->ref_id.
					"&amp;cmd=infoScreen";
				break;

			default:
				$cmd_link = "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
				break;
		}

		return $cmd_link;
	}


	/**
	* Get command target frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		global $ilias;
		
		switch($a_cmd)
		{
			case "view":
				include_once 'payment/classes/class.ilPaymentObject.php';

				if(ilPaymentObject::_isBuyable($this->ref_id) && 
				   !ilPaymentObject::_hasAccess($this->ref_id))
				{					
					$frame = '';
				}
				else
				{
					$frame = "ilContObj".$this->obj_id;
				}
				break;

			case "edit":
				$frame = ilFrameTargetInfo::_getFrame("MainContent");
				break;
				
			case "infoScreen":
				$frame = ilFrameTargetInfo::_getFrame("MainContent");
				break;

			default:
				$frame = "";
				break;
		}

		return $frame;
	}


	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $rbacsystem, $ilUser;

		$props = array();

		include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php");

		if (!ilObjSAHSLearningModuleAccess::_lookupOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}

		if ($rbacsystem->checkAccess($this->ref_id, "write"))
		{
			$props[] = array("alert" => false, "property" => $lng->txt("type"),
				"value" => $lng->txt("sahs"));
		}
		
		if (ilObjSAHSLearningModuleAccess::_lookupCertificate($this->obj_id))
		{
			include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
			$type = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
			switch ($type)
			{
				case "scorm":
					include_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";
					if (ilObjSCORMLearningModule::_getCourseCompletionForUser($this->obj_id, $ilUser->getId()))
					{
						$lng->loadLanguageModule('certificate');
						$this->ctrl->setParameterByClass("ilobjsahslearningmodulegui", "ref_id", $this->ref_id);
						$props[] = array("alert" => false, "property" => $lng->txt("condition_finished"),
							"value" => '<a href="' . $this->ctrl->getLinkTargetByClass("ilobjsahslearningmodulegui", "downloadCertificate") . '">' . $lng->txt("download_certificate") . '</a>');
					}
					break;
				case "scorm2004":
					include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php";
					if (ilObjSCORM2004LearningModule::_getCourseCompletionForUser($this->obj_id, $ilUser->getId()))
					{
						$lng->loadLanguageModule('certificate');
						$this->ctrl->setParameterByClass("ilobjsahslearningmodulegui", "ref_id", $this->ref_id);
						$props[] = array("alert" => false, "property" => $lng->txt("condition_finished"),
							"value" => '<a href="' . $this->ctrl->getLinkTargetByClass("ilobjsahslearningmodulegui", "downloadCertificate") . '">' . $lng->txt("download_certificate") . '</a>');
					}
					break;
				default:
					break;
			}
		}

		return $props;
	}


} // END class.ilObjCategoryGUI
?>
