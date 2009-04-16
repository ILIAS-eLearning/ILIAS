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
* TableGUI class for title/description translations
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesObject
*/
class ilObjectTranslationTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_incl_desc = true)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->incl_desc = $a_incl_desc;
		
		$this->setLimit(9999);
		
		$this->addColumn("", "", "1");
		$this->addColumn($this->lng->txt("language"), "", "");
		$this->addColumn($this->lng->txt("default"), "", "");
		$this->addColumn($this->lng->txt("title"), "", "");
		if ($a_incl_desc)
		{
			$this->addColumn($this->lng->txt("description"), "", "");
		}
//		$this->addColumn($this->lng->txt("actions"), "", "");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.obj_translation_row.html", "Services/Object");
		$this->disable("footer");
		$this->setEnableTitle(true);

		$this->nr = 0;
	}
	
	/**
	* Prepare output
	*/
	function prepareOutput()
	{
		global $lng;

		$this->addMultiCommand("deleteHeaderTitles", $lng->txt("remove"));
		if ($this->dataExists())
		{
			$this->addCommandButton("saveHeaderTitles", $lng->txt("save"));
		}
		$this->addCommandButton("addHeaderTitle", $lng->txt("add"));
	}
	
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;

		$this->nr++;
		
		if ($this->incl_desc)
		{
			$this->tpl->setCurrentBlock("desc_row");
			$this->tpl->setVariable("VAL_DESC", ilUtil::prepareFormOutput($a_set["desc"]));
			$this->tpl->setVariable("DNR", $this->nr);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("NR", $this->nr);
		
		// lang selection
		include_once('Services/MetaData/classes/class.ilMDLanguageItem.php');
		$languages = ilMDLanguageItem::_getLanguages();
		$this->tpl->setVariable("LANG_SELECT",
			ilUtil::formSelect($a_set["lang"], "lang[".$this->nr."]", $languages,
			false, true));

		if ($a_set["default"])
		{
			$this->tpl->setVariable("DEF_CHECKED", "checked=\"checked\"");
		}

		$this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_set["title"]));
	}

}
?>
