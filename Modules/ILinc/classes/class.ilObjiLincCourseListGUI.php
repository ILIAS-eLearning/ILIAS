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
* Class ilObjiLincCourseListGUI
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version		$Id$
*
* @extends ilObjectListGUI
*/


include_once "./classes/class.ilObjectListGUI.php";

class ilObjiLincCourseListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjiLincCourseListGUI()
	{
		$this->ilObjectListGUI();
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = false;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->type = "icrs";
		$this->gui_class_name = "ilobjilinccoursegui";

		// general commands array
		include_once('./Modules/ILinc/classes/class.ilObjiLincCourseAccess.php');
		$this->commands = ilObjiLincCourseAccess::_getCommands();
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
		$cmd_link = "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";

		return $cmd_link;
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
		global $lng, $ilias, $rbacsystem;

		$props = array();

		include_once("./Modules/ILinc/classes/class.ilObjiLincCourse.php");

		if (!ilObjiLincCourse::_isActivated($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}

		if (!$ilias->getSetting("ilinc_active"))
		{
			$props[] = array("alert" => false, "property" => $lng->txt("ilinc_remark"),
				"value" => $lng->txt("ilinc_server_not_active"));
		}
		
		// Display cost centers if active
		if ($ilias->getSetting("ilinc_akclassvalues_active") and $rbacsystem->checkAccess("write", $this->ref_id))
		{
			$akclassvalues = ilObjiLincCourse::_getAKClassValues($this->obj_id);

			$value = "";
			
			if (!empty($akclassvalues[0]))
			{
				$value = $akclassvalues[0];
				$property = $lng->txt("ilinc_akclassvalue");
				
				if (!empty($akclassvalues[1]))
				{
					$value .= " / ".$akclassvalues[1];
					$property = $lng->txt("ilinc_akclassvalues");
				}
			}
			elseif (!empty($akclassvalues[1]))
			{
				$value = $akclassvalues[1];
				$property = $lng->txt("ilinc_akclassvalue");
			}
			else
			{
				$property = $lng->txt("ilinc_akclassvalues");
				$value = $lng->txt("ilinc_no_akclassvalues");
			}
		}
		
		$props[] = array("alert" => false, "property" => $property, "value" => $value);
				
		return $props;
	}
} // END class.ilObjiLincCOurseListGUI
?>
