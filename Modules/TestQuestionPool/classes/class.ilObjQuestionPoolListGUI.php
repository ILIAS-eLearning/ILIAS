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


/**
* Class ilObjQuestionPoolListGUI
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author 		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
* @ingroup ModulesTestQuestionPool
*/


include_once "classes/class.ilObjectListGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilObjQuestionPoolListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjQuestionPoolListGUI()
	{
		$this->ilObjectListGUI();
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->copy_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->type = "qpl";
		$this->gui_class_name = "ilobjquestionpoolgui";

		// general commands array
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPoolAccess.php";
		$this->commands = ilObjQuestionPoolAccess::_getCommands();
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
			case "questions":
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
		global $lng, $ilUser;

		$props = array();

		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		if (!ilObjQuestionPool::_lookupOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
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
		// separate method for this line
		//$cmd_link = "assessment/questionpool.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
		$cmd_link = "ilias.php?baseClass=ilObjQuestionPoolGUI&amp;ref_id=".$this->ref_id."&amp;cmd=$a_cmd";

		return $cmd_link;
	}



} // END class.ilObjTestListGUI
?>
