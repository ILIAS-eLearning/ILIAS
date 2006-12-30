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
* BlockGUI class for Selected Items on Personal Desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilPDSelectedItemsBlockGUI extends ilBlockGUI
{
	
	/**
	* Constructor
	*/
	function ilPDSelectedItemsBlockGUI($a_parent_class, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng, $ilUser;
		
		parent::ilBlockGUI($a_parent_class, $a_parent_cmd);
		
		//$this->setImage(ilUtil::getImagePath("icon_bm_s.gif"));
		$this->setTitle($lng->txt("selected_items"));
		$this->setEnableNumInfo(false);
		$this->setLimit(99999);
		$this->setColSpan(2);
		$this->setBlockIdentification("pditems", $ilUser->getId());
		$this->setPrefix("pditems");
		$this->setAvailableDetailLevels(3, 1);
		
	}
	
	function getHTML()
	{
		return parent::getHTML();
	}
	
	function getContent()
	{
		return $this->content;
	}
	
	function setContent($a_content)
	{
		$this->content = $a_content;
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilUser;
		
		$this->tpl->setVariable("BLOCK_ROW", $this->getContent());
		$this->fillBlockFooter();
	}
	

	/**
	* block footer
	*/
	function fillBlockFooter()
	{
		global $ilCtrl, $lng, $ilUser;
		
		// by type
		if ($ilUser->getPref("pd_order_items") == 'location')
		{
			$this->tpl->setCurrentBlock("foot_link");
			$this->tpl->setVariable("FHREF",
				$ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui",
				"orderPDItemsByType"));
			$this->tpl->setVariable("FLINK", $lng->txt("by_type"));
		}
		else
		{
			$this->tpl->setCurrentBlock("foot_text");
			$this->tpl->setVariable("FTEXT", $lng->txt("by_type"));
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->touchBlock("foot_item");
		
		$this->tpl->touchBlock("foot_delim");
		$this->tpl->touchBlock("foot_item");

		// by location
		if ($ilUser->getPref("pd_order_items") == 'location')
		{
			$this->tpl->setCurrentBlock("foot_text");
			$this->tpl->setVariable("FTEXT", $lng->txt("by_location"));
		}
		else
		{
			$this->tpl->setCurrentBlock("foot_link");
			$this->tpl->setVariable("FHREF",
				$ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui",
				"orderPDItemsByLocation"));
			$this->tpl->setVariable("FLINK", $lng->txt("by_location"));
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->touchBlock("foot_item");

		$this->tpl->setCurrentBlock("block_footer");
		$this->tpl->setVariable("FCOLSPAN", $this->getColSpan());
		$this->tpl->parseCurrentBlock();
	}


	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
		return '<div class="small">'.$this->num_bookmarks." ".$lng->txt("bm_num_bookmarks").", ".
			$this->num_folders." ".$lng->txt("bm_num_bookmark_folders")."</div>";
	}

}

?>
