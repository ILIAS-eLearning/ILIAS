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

require_once("./Modules/ScormAicc/classes/class.ilObjAICCLearningModuleGUI.php");
require_once("./Modules/ScormAicc/classes/class.ilObjHACPLearningModule.php");

/**
* Class ilObjHACPLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjHACPLearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjHACPLearningModuleGUI: ilInfoScreenGUI
* @ilCtrl_Calls ilObjHACPLearningModuleGUI: ilLicenseGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjHACPLearningModuleGUI extends ilObjAICCLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjHACPLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		#$this->tabs_gui =& new ilTabsGUI();

	}


	/**
	* assign hacp object to hacp gui object
	*/
	function assignObject()
	{
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& new ilObjHACPLearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjHACPLearningModule($this->id, false);
			}
		}
	}

	

} // END class.ilObjAICCLearningModule
?>
