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
* Class ilInfoScreenGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
class ilInfoScreenGUI
{
	var $ilias;
	var $lng;
	var $ctrl;
	var $gui_object;

	/**
	* Constructor
	*
	* @param	object	$a_gui_object	GUI instance of related object
	* 									(ilCouseGUI, ilTestGUI, ...)
	*/
	function ilInfoScreenGUI($a_gui_object)
	{
		global $ilias, $ilCtrl, $lng;

		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->gui_object =& $a_gui_object;
		$this->sec_nr = 0;
		$this->private_notes_enabled = false;
	}
	
	/**
	* enable notes
	*/
	function enablePrivateNotes($a_enable = true)
	{
		$this->private_notes_enabled = $a_enable;
	}
	
	/**
	* add a new section
	*/
	function addSection($a_title)
	{
		$this->sec_nr++;
		$this->section[$this->sec_nr]["title"] = $a_title;
	}
	
	/**
	* add a property to current section
	*/
	function addProperty($a_name, $a_value)
	{
		$this->section[$this->sec_nr]["properties"][] =
			array("name" => $a_name, "value" => $a_value);
	}
	
	/**
	* get html
	*/
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.infoscreen.html" ,true, true);
		for($i = 1; $i <= $this->sec_nr; $i++)
		{
			// section header
			$tpl->setCurrentBlock("header_row");
			$tpl->setVariable("TXT_SECTION",
				$this->section[$i]["title"]);
			$tpl->parseCurrentBlock();
			$tpl->touchBlock("row");
			
			// section properties
			foreach($this->section[$i]["properties"] as $property)
			{
				if ($property["name"] != "")
				{
					$tpl->setCurrentBlock("property_row");
					$tpl->setVariable("TXT_PROPERTY", $property["name"]);
					$tpl->setVariable("TXT_PROPERTY_VALUE", $property["value"]);
					$tpl->parseCurrentBlock();
					$tpl->touchBlock("row");
				}
				else
				{
					$tpl->setCurrentBlock("property_full_row");
					$tpl->setVariable("TXT_PROPERTY_FULL_VALUE", $property["value"]);
					$tpl->parseCurrentBlock();
					$tpl->touchBlock("row");
				}
			}
		}
		
		// notes section
		if ($this->private_notes_enabled)
		{
			$html = $this->showNotesSection();
			$tpl->setCurrentBlock("notes");
			$tpl->setVariable("NOTES", $html);
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}
	
	
	/**
	* show notes section
	*/
	function showNotesSection()
	{
		$next_class = $this->ctrl->getNextClass($this);

		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		$notes_gui = new ilNoteGUI($this->gui_object->object->getId(), 0,
			$this->gui_object->object->getType());
		
		$notes_gui->enablePrivateNotes();
		//$notes_gui->enablePublicNotes();

		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{	
			$html = $notes_gui->getNotesHTML();
		}
		
		return $html;
	}

}

?>
