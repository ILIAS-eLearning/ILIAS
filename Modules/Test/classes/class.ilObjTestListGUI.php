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
* Class ilObjTestListGUI
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
* @ingroup ModulesTest
*/


include_once "classes/class.ilObjectListGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilObjTestListGUI extends ilObjectListGUI
{
	protected $command_link_params = array();
	
	/**
	* constructor
	*
	*/
	function ilObjTestListGUI()
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
		$this->payment_enabled = true;
		$this->type = "tst";
		$this->gui_class_name = "ilobjtestgui";

		// general commands array
		include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
		$this->commands = ilObjTestAccess::_getCommands();
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
			case "eval_a":
			case "eval_stat":
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
		include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
		if (!ilObjTestAccess::_lookupCreationComplete($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("tst_warning_test_not_complete"));
		}
		$onlineaccess = ilObjTestAccess::_lookupOnlineTestAccess($this->obj_id, $ilUser->id);
		if ($onlineaccess !== true)
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $onlineaccess);
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
		$cmd_link = "ilias.php?baseClass=ilObjTestGUI&amp;ref_id=".$this->ref_id."&amp;cmd=$a_cmd";
		
		foreach($this->command_link_params as $param => $value)
		{
			$cmd_link .= '&'.$param.'='.$value;
		}

		return $cmd_link;
	}
	

	/**
	 * add command link parameters
	 *
	 * @access public
	 * @param array (param => value)
	 * @return
	 */
	public function addCommandLinkParameter($a_param)
	{
		$this->command_link_params = $a_param;
	}


} // END class.ilObjTestListGUI
?>
