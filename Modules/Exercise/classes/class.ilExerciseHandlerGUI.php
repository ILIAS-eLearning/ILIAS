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
* Handles user interface for exercises
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilExerciseHandlerGUI: ilObjExerciseGUI
*
* @ingroup ModulesExercise
*/
class ilExerciseHandlerGUI
{
	function ilExerciseHandlerGUI()
	{
		global $ilCtrl, $lng, $ilAccess, $ilias, $ilNavigationHistory;

		// initialisation stuff
		$this->ctrl =&  $ilCtrl;
		
		//$ilNavigationHistory->addItem($_GET["ref_id"],
		//	"ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$_GET["ref_id"]);

	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess, $tpl;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		if ($next_class == "")
		{
			$this->ctrl->setCmdClass("ilobjexercisegui");
			$next_class = $this->ctrl->getNextClass($this);
		}

		switch ($next_class)
		{
			case 'ilobjexercisegui':
				require_once "./Modules/Exercise/classes/class.ilObjExerciseGUI.php";
				$ex_gui =& new ilObjExerciseGUI("", (int) $_GET["ref_id"], true, false);
				$this->ctrl->forwardCommand($ex_gui);
				break;
		}

		$tpl->show();
	}

}
