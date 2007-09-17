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

require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjSCORMLearningModuleGUI.php 13133 2007-01-30 11:13:06Z akill $
*
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilInfoScreenGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORM2004LearningModuleGUI extends ilObjSCORMLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSCORM2004LearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		#$this->tabs_gui =& new ilTabsGUI();
	}

	/**
	* assign scorm object to scorm gui object
	*/
	function assignObject()
	{
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& new ilObjSCORM2004LearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjSCORM2004LearningModule($this->id, false);
			}
		}
	}
	
	/**
	* show tracking data
	*/
	function showTrackingItems()
	{
		global $lng, $tpl;
		
		include_once("./Modules/SCORM2004/classes/class.ilSCORM2004TrackingTableGUI.php");
		$table_gui = new ilSCORM2004TrackingTableGUI($this, "showTrackingItems");
				
		$tr_data_sets = $this->object->getTrackedUsers();
		$table_gui->setTitle($lng->txt("cont_tracking_data"));
		$table_gui->setData($tr_data_sets);
		$tpl->setContent($table_gui->getHTML());
	}
	
	function deleteTrackingData()
	{
		if (is_array($_POST["id"]))
		{
			$this->object->deleteTrackingDataOfUsers($_POST["id"]);
		}
		$this->showTrackingItems();
	}

} // END class.ilObjSCORM2004LearningModuleGUI
?>
