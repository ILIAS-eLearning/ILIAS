<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for 
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup Services
*/
class ilConfirmationTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_use_icons)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->use_icons = $a_use_icons;
		
		parent::__construct(null, "");
		$this->setTitle($lng->txt(""));
		$this->setLimit(9999);
		
		if ($this->use_icons)
		{
			$this->addColumn($this->lng->txt("type"), "", "1");
		}
		$this->addColumn($this->lng->txt("title"));
		
		$this->setEnableHeader(true);
		$this->setRowTemplate("tpl.confirmation_row.html", "Services/Utilities");
		$this->disable("footer");
		$this->setEnableTitle(true);
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($item)
	{
		global $lng;

		if ($this->use_icons)
		{
			if ($item["img"] != "")
			{
				$this->tpl->setCurrentBlock("img_cell");
				$this->tpl->setVariable("IMG_ITEM", $item["img"]);
				$this->tpl->setVariable("ALT_ITEM", $item["alt"]);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->touchBlock("blank_cell");
			}
		}
		$this->tpl->setVariable("TXT_ITEM", $item["text"]);
		$this->tpl->setVariable("VAR_ITEM", $item["var"]);
		$this->tpl->setVariable("ID", $item["id"]);
	}

}
?>
