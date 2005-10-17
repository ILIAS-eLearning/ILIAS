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
* Private Notes on PD
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/


class ilPDNotesGUI
{

	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilPDNotesGUI()
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
			default:
				$cmd = $this->ctrl->getCmd("view");
				$this->displayHeader();
				$this->$cmd();
				break;
		}
		$this->tpl->show(true);
		return true;
	}

	/**
	* display header and locator
	*/
	function displayHeader()
	{
		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_pd_b.gif"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("HEADER",$this->lng->txt("personal_desktop"));
		
		// set locator
		$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
		$this->tpl->touchBlock("locator_separator");
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("LINK_ITEM",
			$this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui"));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("private_notes"));
		$this->tpl->setVariable("LINK_ITEM",
			$this->ctrl->getLinkTargetByClass("ilpdnotesgui"));
		$this->tpl->parseCurrentBlock();
		
		// catch feedback message
		sendInfo();
		// display infopanel if something happened
		infoPanel();

	}

	/*
	* display notes
	*/
	function view()
	{
		//$this->tpl->addBlockFile("ADM_CONTENT", "objects", "tpl.table.html")
		$this->tpl->setVariable("ADM_CONTENT", "Work in progress...");;
	}

}
?>
