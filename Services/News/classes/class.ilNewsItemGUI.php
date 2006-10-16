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

define("IL_FORM_EDIT", 0);
define("IL_FORM_CREATE", 1);
define("IL_FORM_REENTER", 2);

/**
* User Interface for NewsItem entities.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsItemGUI 
{

	private $edit_mode;

	/**
	* Constructor.
	*
	*/
	public function __construct()
	{
		global $ilCtrl;
		
		$this->ctrl = $ilCtrl;
		
		
		if ($_GET["news_item_id"] > 0)
		{
			$this->news_item = new ilNewsItem($_GET["news_item_id"]);
		}
		
		$this->ctrl->saveParameter($this, array("news_item_id"));

	}

	/**
	* Execute command.
	*
	*/
	public function &executeCommand()
	{
		global $ilCtrl;
		
		// get next class and command
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch ($next_class)
		{
			default:
				$html = $this->$cmd();
				break;
		}
		
		return $html;

	}

	/**
	* Set EditMode.
	*
	* @param	int	$a_edit_mode	Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_REENTER)
	*/
	public function setEditMode($a_edit_mode)
	{
		$this->edit_mode = $a_edit_mode;
	}

	/**
	* Get EditMode.
	*
	* @return	int	Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_REENTER)
	*/
	public function getEditMode()
	{
		return $this->edit_mode;
	}

	/**
	* Output NewsItem form.
	*
	*/
	public function outputForm()
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.property_form.html", true, true);
		$values = $this->getValues();
		
		$tpl->setCurrentBlock("prop_Varchar");
		$tpl->setVariable("POST_VAR", "news_title");
		$tpl->setVariable("PROPERTY_TITLE", $lng->txt("news_title"));
		$tpl->setVariable("PROPERTY_VALUE",
			ilUtil::prepareFormOutput($values["Title"]));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("prop_Text");
		$tpl->setVariable("POST_VAR", "news_content");
		$tpl->setVariable("PROPERTY_TITLE", $lng->txt("news_content"));
		$tpl->setVariable("PROPERTY_VALUE",
			ilUtil::prepareFormOutput($values["Content"]));
		$tpl->parseCurrentBlock();
		
		
		// save and cancel commands
		$tpl->setCurrentBlock("cmd");
		$tpl->setVariable("CMD", "update");
		$tpl->setVariable("CMD_TXT", $lng->txt["save"]);
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("cmd");
		$tpl->setVariable("CMD", "cancelUpdate");
		$tpl->setVariable("CMD_TXT", $lng->txt["cancel"]);
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction());
		return $tpl->get();

	}

	/**
	* Edit NewsItem.
	*
	*/
	public function edit()
	{
		$this->setEditMode(IL_FORM_EDIT);
		$this->outputForm();

	}

	/**
	* Create NewsItem.
	*
	*/
	public function create()
	{
		$this->setEditMode(IL_FORM_CREATE);
		$this->outputForm();

	}

	/**
	* Update NewsItem.
	*
	*/
	public function update()
	{
		if ($this->checkInput())
		{
			
			$this->news_item->setTitle(ilUtil::stripSlashes($_POST["news_title"]));
			$this->news_item->setContent(ilUtil::stripSlashes($_POST["news_content"]));
			$this->news_item->update();
		}
		else
		{
			$this->setEditMode(IL_FORM_REENTER);
			$this->outputForm();
		}

	}

	/**
	* Get current values for NewsItem form.
	*
	*/
	public function getValues()
	{
		$values = array();
		
		switch ($this->getEditMode())
		{
			case IL_FORM_CREATE:
				$values["Title"] = "tt";
				$values["Content"] = "";
				break;
				
			case IL_FORM_EDIT:
				$values["Title"] = $this->news_item->getTitle();
				$values["Content"] = $this->news_item->getContent();
				break;
				
			case IL_FORM_REENTER:
				$values["Title"] = ilUtil::stripSlashes($_POST["news_title"]);
				$values["Content"] = ilUtil::stripSlashes($_POST["news_content"]);
				break;
		}
		
		return $values;

	}


}
