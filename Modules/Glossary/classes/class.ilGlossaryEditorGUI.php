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
* Class ilGlossaryEditorGUI
*
* GUI class for Glossary Editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilGlossaryEditorGUI: ilObjGlossaryGUI
*
* @package Modules/Glossary
*/
class ilGlossaryEditorGUI
{
	function ilGlossaryEditorGUI()
	{
		global $ilCtrl, $lng, $ilAccess, $ilias;
		
		// initialisation stuff
		$this->ctrl =&  $ilCtrl;
		$lng->loadLanguageModule("content");
		
		// check write permission
		if (!$ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		if ($next_class == "")
		{
			$this->ctrl->setCmdClass("ilobjglossarygui");
			$this->ctrl->setCmd("");
		}

		switch ($next_class)
		{
			case 'ilobjglossarygui':
			default:
				require_once "./content/classes/class.ilObjGlossaryGUI.php";
				$glossary_gui =& new ilObjGlossaryGUI("", $_GET["ref_id"], true, false);
				$this->ctrl->forwardCommand($glossary_gui);
				break;
		}
	}

}
