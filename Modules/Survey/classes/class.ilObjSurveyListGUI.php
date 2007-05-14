<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


include_once "classes/class.ilObjectListGUI.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Class ilObjSurveyListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* $Id$
*
* @extends ilObjectListGUI
* @ingroup ModulesSurvey
*/
class ilObjSurveyListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjSurveyListGUI()
	{
		$this->ilObjectListGUI();
		$this->info_screen_enabled = true;
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->type = "svy";
		$this->gui_class_name = "ilobjsurveygui";

		// general commands array
		include_once('./Modules/Survey/classes/class.ilObjSurveyAccess.php');
		$this->commands = ilObjSurveyAccess::_getCommands();
	}


	/**
	* inititialize new item
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
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
		switch($a_cmd)
		{
			case "":
			case "infoScreen":
			case "evaluation":
				include_once "./classes/class.ilFrameTargetInfo.php";
				$frame = ilFrameTargetInfo::_getFrame("MainContent");
				break;

			default:
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
		global $lng, $ilUser, $rbacsystem;

		$props = array();

		if (!$rbacsystem->checkAccess("visible,read", $this->ref_id))
		{
			return $props;
		}
		
		include_once("./Modules/Survey/classes/class.ilObjSurveyAccess.php");
		if (!ilObjSurveyAccess::_lookupOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}
		else
		{
			if (!ilObjSurveyAccess::_lookupCreationComplete($this->obj_id))
			{
				// no completion
				$props[] = array("alert" => true, "property" => $lng->txt("status"),
					"value" => $lng->txt("svy_warning_survey_not_complete"));
			}
			else
			{
				if ($ilUser->getId() != ANONYMOUS_USER_ID)
				{
					$finished = ilObjSurveyAccess::_lookupFinished($this->obj_id, $ilUser->id);
		
					// finished
					if ($finished === 1)
					{
						$stat = $this->lng->txt("svy_finished");
					}
					// not finished
					else if ($finished === 0)
					{
						$stat = $this->lng->txt("svy_not_finished");
					}
					// not started
					else
					{
						$stat = $this->lng->txt("svy_not_started");
					}
					$props[] = array("alert" => false, "property" => $lng->txt("status"),
						"value" => $stat);
				}
			}
		}

		return $props;
	}


	/**
	* Get command link url.
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{
		$cmd_link = "";
		switch ($a_cmd)
		{
			default:
				$cmd_link = "ilias.php?baseClass=ilObjSurveyGUI&amp;ref_id=" . $this->ref_id .
					"&amp;cmd=$a_cmd";
				break;
		}
		// separate method for this line
		return $cmd_link;
	}



} // END class.ilObjTestListGUI
?>
