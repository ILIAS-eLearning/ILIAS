<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for style editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesStyle
*/
class ilStyleTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_chars, $a_super_type, $a_style)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->media_object = $a_media_object;
		$this->super_type = $a_super_type;
		$this->style = $a_style;
		$all_super_types = ilObjStyleSheet::_getStyleSuperTypes();
		$this->types = $all_super_types[$this->super_type];
		$this->core_styles = ilObjStyleSheet::_getCoreStyles();
		$this->setData($a_chars);
		$this->setTitle($lng->txt("sty_".$a_super_type."_char"));
		$this->setLimit(9999);
		
		// check, whether any of the types is expandable
		$this->expandable = false;
		$this->hideable = false;
		foreach ($this->types as $t)
		{
			if (ilObjStyleSheet::_isExpandable($t))
			{
				$this->expandable = true;
			}
			if (ilObjStyleSheet::_isHideable($t))
			{
				$this->hideable = true;
			}
		}

		if ($this->expandable)
		{
			$this->addColumn("", "", "1");	// checkbox
		}
		$this->addColumn($this->lng->txt("sty_name"), "", "1");
		$this->addColumn($this->lng->txt("sty_type"), "", "");
		$this->addColumn($this->lng->txt("sty_example"), "", "");
		if ($this->hideable)
		{
			$this->addColumn($this->lng->txt("sty_hide"), "", "");	// hide checkbox
		}
		$this->addColumn($this->lng->txt("sty_commands"), "", "1");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.style_row.html", "Services/Style");
		$this->disable("footer");

		// action commands
		if ($this->hideable)
		{
			$this->addCommandButton("saveHideStatus", $lng->txt("sty_save_hide_status"));
		}
		
		// action commands
		if ($this->expandable)
		{
			$this->addMultiCommand("deleteCharacteristicConfirmation", $lng->txt("delete"));
			$this->addCommandButton("addCharacteristicForm", $lng->txt("sty_add_characteristic"));
		}
		
		$this->setEnableTitle(true);
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;

		$stypes = ilObjStyleSheet::_getStyleSuperTypes();
		$types = $stypes[$this->super_type];
		
		if (!in_array($a_set["type"], $types))
		{
			return;
		}
//var_dump($a_set);
		// checkbox row
		if ($this->expandable)
		{
			if (!empty($this->core_styles[$a_set["type"].".".
				ilObjStyleSheet::_determineTag($a_set["type"]).
				".".$a_set["class"]]))
			{
				$this->tpl->touchBlock("no_checkbox");
			}
			else
			{
				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHAR", $a_set["type"].".".
					ilObjStyleSheet::_determineTag($a_set["type"]).
					".".$a_set["class"]);
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($this->hideable)
		{
			if (!ilObjStyleSheet::_isHideable($a_set["type"]))
			{
				$this->tpl->touchBlock("no_hide_checkbox");
			}
			else
			{
				$this->tpl->setCurrentBlock("hide_checkbox");
				$this->tpl->setVariable("CHAR", $a_set["type"].".".
					ilObjStyleSheet::_determineTag($a_set["type"]).
					".".$a_set["class"]);
				if ($this->style->getHideStatus($a_set["type"], $a_set["class"]))
				{
					$this->tpl->setVariable("CHECKED", "checked='checked'");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		
		// example
		$this->tpl->setVariable("EXAMPLE",
			ilObjStyleSheetGUI::getStyleExampleHTML($a_set["type"], $a_set["class"]));

		$tag_str = ilObjStyleSheet::_determineTag($a_set["type"]).".".$a_set["class"];
		$this->tpl->setVariable("TXT_TAG", $a_set["class"]);
		$this->tpl->setVariable("TXT_TYPE", $lng->txt("sty_type_".$a_set["type"]));
		$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
		$ilCtrl->setParameter($this->parent_obj, "tag", $tag_str);
		$ilCtrl->setParameter($this->parent_obj, "style_type", $a_set["type"]);
		$this->tpl->setVariable("LINK_EDIT_TAG_STYLE",
			$ilCtrl->getLinkTarget($this->parent_obj, "editTagStyle"));

	}

}
?>
