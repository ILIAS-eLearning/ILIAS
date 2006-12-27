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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for block NewsForContext
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsForContextBlockGUI extends ilBlockGUI
{
	
	/**
	* Constructor
	*/
	function ilNewsForContextBlockGUI($a_parent_class, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI($a_parent_obj, $a_parent_cmd);
		
		$this->setImage(ilUtil::getImagePath("icon_news_s.gif"));
	}
	
	/**
	* Set EnableEdit.
	*
	* @param	boolean	$a_enable_edit	Edit mode on/off
	*/
	public function setEnableEdit($a_enable_edit = 0)
	{
		$this->enable_edit = $a_enable_edit;
	}

	/**
	* Get EnableEdit.
	*
	* @return	boolean	Edit mode on/off
	*/
	public function getEnableEdit()
	{
		return $this->enable_edit;
	}

	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng;
		
		// add edit commands
		if ($this->getEnableEdit())
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilnewsitemgui", "editNews"),
				$lng->txt("edit"));

			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilnewsitemgui", "createNewsItem"),
				$lng->txt("add"));
		}

		return parent::getHTML();
	}

}

?>
