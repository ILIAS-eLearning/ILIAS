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
define("IL_FORM_EDIT", 0);
define("IL_FORM_CREATE", 1);
define("IL_FORM_RE_EDIT", 2);
define("IL_FORM_RE_CREATE", 3);

/**
* GUI class for HTML Block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilHtmlBlockGUIGen extends ilBlockGUI
{

	protected $form_edit_mode;

	/**
	* Constructor.
	*
	*/
	public function __construct()
	{
		global $ilCtrl;
		
		$this->ctrl = $ilCtrl;
		
		
		include_once("Services/Block/classes/class.ilHtmlBlock.php");
		if ($_GET["html_block_id"] > 0)
		{
			$this->html_block = new ilHtmlBlock($_GET["html_block_id"]);
		}
		
		$this->ctrl->saveParameter($this, array("html_block_id"));
		

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
	* Set FormEditMode.
	*
	* @param	int	$a_form_edit_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_RE_EDIT | IL_FORM_RE_CREATE)
	*/
	public function setFormEditMode($a_form_edit_mode)
	{
		$this->form_edit_mode = $a_form_edit_mode;
	}

	/**
	* Get FormEditMode.
	*
	* @return	int	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_RE_EDIT | IL_FORM_RE_CREATE)
	*/
	public function getFormEditMode()
	{
		return $this->form_edit_mode;
	}

	/**
	* FORM HtmlBlock: Output form.
	*
	*/
	public function outputFormHtmlBlock()
	{
		global $lng;
		
		$lng->loadLanguageModule("block");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$form_gui = new ilPropertyFormGUI();
		
		$values = $this->getValuesHtmlBlock();
		
		// Property Title
		$alert = ($this->form_check["HtmlBlock"]["Title"]["error"] != "")
			? $this->form_check["HtmlBlock"]["Title"]["error"]
			: "";
		$form_gui->addTextProperty($lng->txt("block_html_block_title"),
			"block_title",
			$values["Title"],
			"", $alert, true
			, "200");
		
		// Property Content
		$alert = ($this->form_check["HtmlBlock"]["Content"]["error"] != "")
			? $this->form_check["HtmlBlock"]["Content"]["error"]
			: "";
		$form_gui->addTextAreaProperty($lng->txt("block_html_block_content"),
			"block_content",
			$values["Content"],
			"", $alert, false
			, "40", "8", true);
		
		// save and cancel commands
		if (in_array($this->getFormEditMode(), array(IL_FORM_CREATE,IL_FORM_RE_CREATE)))
		{
			$form_gui->addCommandButton("saveHtmlBlock", $lng->txt("save"));
			$form_gui->addCommandButton("cancelSaveHtmlBlock", $lng->txt("cancel"));
		}
		else
		{
			$form_gui->addCommandButton("updateHtmlBlock", $lng->txt("save"));
			$form_gui->addCommandButton("cancelUpdateHtmlBlock", $lng->txt("cancel"));
		}
		
		$form_gui->setTitle($lng->txt("block_html_block_head"));
		$form_gui->setFormAction($this->ctrl->getFormAction($this));
		
		// individual preparation of form
		$this->prepareFormHtmlBlock($form_gui);
		
		return $form_gui->getHTML();

	}

	/**
	* FORM HtmlBlock: Edit form.
	*
	*/
	public function editHtmlBlock()
	{
		$this->setFormEditMode(IL_FORM_EDIT);
		return $this->outputFormHtmlBlock();

	}

	/**
	* FORM HtmlBlock: Create HtmlBlock.
	*
	*/
	public function createHtmlBlock()
	{
		$this->setFormEditMode(IL_FORM_CREATE);
		return $this->outputFormHtmlBlock();

	}

	/**
	* FORM HtmlBlock: Save HtmlBlock.
	*
	*/
	public function saveHtmlBlock()
	{
		include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
		if ($this->checkInputHtmlBlock())
		{
			$this->html_block = new ilHtmlBlock();
			$this->html_block->setTitle(ilUtil::stripSlashes($_POST["block_title"]));
			$this->html_block->setContent(ilUtil::stripSlashes($_POST["block_content"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString()));
			$this->prepareSaveHtmlBlock($this->html_block);
			$this->html_block->create();
		}
		else
		{
			$this->setFormEditMode(IL_FORM_RE_CREATE);
			return $this->outputFormHtmlBlock();
		}

	}

	/**
	* FORM HtmlBlock: Update HtmlBlock.
	*
	*/
	public function updateHtmlBlock()
	{
		include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
		if ($this->checkInputHtmlBlock())
		{
			
			$this->html_block->setTitle(ilUtil::stripSlashes($_POST["block_title"]));
			$this->html_block->setContent(ilUtil::stripSlashes($_POST["block_content"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString()));
			$this->html_block->update();
		}
		else
		{
			$this->setFormEditMode(IL_FORM_RE_EDIT);
			return $this->outputFormHtmlBlock();
		}

	}

	/**
	* FORM HtmlBlock: Get current values for HtmlBlock form.
	*
	*/
	public function getValuesHtmlBlock()
	{
		$values = array();
		
		switch ($this->getFormEditMode())
		{
			case IL_FORM_CREATE:
				$values["Title"] = "";
				$values["Content"] = "";
				break;
				
			case IL_FORM_EDIT:
				$values["Title"] = $this->html_block->getTitle();
				$values["Content"] = $this->html_block->getContent();
				break;
				
			case IL_FORM_RE_EDIT:
			case IL_FORM_RE_CREATE:
				$values["Title"] = ilUtil::stripSlashes($_POST["block_title"]);
				$values["Content"] = ilUtil::stripSlashes($_POST["block_content"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString());
				break;
		}
		
		return $values;

	}

	/**
	* FORM HtmlBlock: Check input.
	*
	*/
	public function checkInputHtmlBlock()
	{
		
		include_once("./Services/Utilities/classes/class.ilTypeCheck.php");
		$ilTypeCheck = new ilTypeCheck();
		
		$this->form_check["HtmlBlock"] = array();
		$this->form_check["HtmlBlock"]["Title"] =
			ilTypeCheck::check("varchar", $_POST["block_title"], true);
		$this->form_check["HtmlBlock"]["Content"] =
			ilTypeCheck::check("text", $_POST["block_content"], false);
		
		foreach($this->form_check["HtmlBlock"] as $prop_check)
		{
			if (!$prop_check["ok"])
			{
				return false;
			}
		}
		return true;

	}

	/**
	* FORM HtmlBlock: Prepare Saving of HtmlBlock.
	*
	* @param	object	$a_html_block	HtmlBlock object.
	*/
	public function prepareSaveHtmlBlock(&$a_html_block)
	{

	}

	/**
	* FORM HtmlBlock: Prepare form. (Can be overwritten in derived classes)
	*
	* @param	object	$a_form_gui	ilPropertyFormGUI instance.
	*/
	public function prepareFormHtmlBlock(&$a_form_gui)
	{

	}


}
?>
