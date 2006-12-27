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
define("IL_FORM_RE_EDIT", 2);
define("IL_FORM_RE_CREATE", 3);

/**
* User Interface for NewsItem entities.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsItemGUIGen 
{

	private $enable_edit = 0;
	private $context_obj_id;
	private $context_obj_type;
	private $context_sub_obj_id;
	private $context_sub_obj_type;
	private $form_edit_mode;

	/**
	* Constructor.
	*
	*/
	public function __construct()
	{
		global $ilCtrl;
		
		$this->ctrl = $ilCtrl;
		
		
		include_once("Services/News/classes/class.ilNewsItem.php");
		if ($_GET["news_item_id"] > 0)
		{
			$this->news_item = new ilNewsItem($_GET["news_item_id"]);
		}
		
		$this->ctrl->saveParameter($this, array("news_item_id"));
		
		// Init EnableEdit.
		$this->setEnableEdit(false);
		
		// Init Context.
		$this->setContextObjId($ilCtrl->getContextObjId());
		$this->setContextObjType($ilCtrl->getContextObjType());
		$this->setContextSubObjId($ilCtrl->getContextSubObjId());
		$this->setContextSubObjType($ilCtrl->getContextSubObjType());
		

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
	* Set ContextObjId.
	*
	* @param	int	$a_context_obj_id	
	*/
	public function setContextObjId($a_context_obj_id)
	{
		$this->context_obj_id = $a_context_obj_id;
	}

	/**
	* Get ContextObjId.
	*
	* @return	int	
	*/
	public function getContextObjId()
	{
		return $this->context_obj_id;
	}

	/**
	* Set ContextObjType.
	*
	* @param	int	$a_context_obj_type	
	*/
	public function setContextObjType($a_context_obj_type)
	{
		$this->context_obj_type = $a_context_obj_type;
	}

	/**
	* Get ContextObjType.
	*
	* @return	int	
	*/
	public function getContextObjType()
	{
		return $this->context_obj_type;
	}

	/**
	* Set ContextSubObjId.
	*
	* @param	int	$a_context_sub_obj_id	
	*/
	public function setContextSubObjId($a_context_sub_obj_id)
	{
		$this->context_sub_obj_id = $a_context_sub_obj_id;
	}

	/**
	* Get ContextSubObjId.
	*
	* @return	int	
	*/
	public function getContextSubObjId()
	{
		return $this->context_sub_obj_id;
	}

	/**
	* Set ContextSubObjType.
	*
	* @param	int	$a_context_sub_obj_type	
	*/
	public function setContextSubObjType($a_context_sub_obj_type)
	{
		$this->context_sub_obj_type = $a_context_sub_obj_type;
	}

	/**
	* Get ContextSubObjType.
	*
	* @return	int	
	*/
	public function getContextSubObjType()
	{
		return $this->context_sub_obj_type;
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
	* FORM NewsItem: Output form.
	*
	*/
	public function outputFormNewsItem()
	{
		global $lng;
		
		$lng->loadLanguageModule("news");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$form_gui = new ilPropertyFormGUI();
		
		$values = $this->getValuesNewsItem();
		
		// Property Title
		$alert = ($this->form_check["NewsItem"]["Title"]["error"] != "")
			? $this->form_check["NewsItem"]["Title"]["error"]
			: "";
		$form_gui->addTextProperty($lng->txt("news_title"),
			"news_title",
			$values["Title"],
			"", $alert, true
			, "200");
		
		// Property Content
		$alert = ($this->form_check["NewsItem"]["Content"]["error"] != "")
			? $this->form_check["NewsItem"]["Content"]["error"]
			: "";
		$form_gui->addTextAreaProperty($lng->txt("news_content"),
			"news_content",
			$values["Content"],
			"", $alert, false
			, "40", "8", true);
		
		// Property Visibility
		$alert = ($this->form_check["NewsItem"]["Visibility"]["error"] != "")
			? $this->form_check["NewsItem"]["Visibility"]["error"]
			: "";
		$form_gui->addRadioProperty($lng->txt("news_visibility"),
			"news_visibility",array(
				array("value" => "users", "text" => $lng->txt("news_visibility_users")), 
				array("value" => "public", "text" => $lng->txt("news_visibility_public"))),
			$values["Visibility"],
			$lng->txt("news_visibility_info"), $alert, false);
		
		// Property ContentLong
		$alert = ($this->form_check["NewsItem"]["ContentLong"]["error"] != "")
			? $this->form_check["NewsItem"]["ContentLong"]["error"]
			: "";
		$form_gui->addTextAreaProperty($lng->txt("news_content_long"),
			"news_content_long",
			$values["ContentLong"],
			$lng->txt("news_content_long_info"), $alert, false
			, "40", "8", true);
		
		// save and cancel commands
		if (in_array($this->getFormEditMode(), array(IL_FORM_CREATE,IL_FORM_RE_CREATE)))
		{
			$form_gui->addCommandButton("saveNewsItem", $lng->txt("save"));
			$form_gui->addCommandButton("cancelSaveNewsItem", $lng->txt("cancel"));
		}
		else
		{
			$form_gui->addCommandButton("updateNewsItem", $lng->txt("save"));
			$form_gui->addCommandButton("cancelUpdateNewsItem", $lng->txt("cancel"));
		}
		
		$form_gui->setTitle($lng->txt("news_news_item_head"));
		$form_gui->setFormAction($this->ctrl->getFormAction($this));
		
		// individual preparation of form
		$this->prepareFormNewsItem($form_gui);
		
		return $form_gui->getHTML();

	}

	/**
	* FORM NewsItem: Edit form.
	*
	*/
	public function editNewsItem()
	{
		$this->setFormEditMode(IL_FORM_EDIT);
		return $this->outputFormNewsItem();

	}

	/**
	* FORM NewsItem: Create NewsItem.
	*
	*/
	public function createNewsItem()
	{
		$this->setFormEditMode(IL_FORM_CREATE);
		return $this->outputFormNewsItem();

	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	public function saveNewsItem()
	{
		include_once("./classes/class.ilObjAdvancedEditing.php");
		if ($this->checkInputNewsItem())
		{
			$this->news_item = new ilNewsItem();
			$this->news_item->setTitle(ilUtil::stripSlashes($_POST["news_title"]));
			$this->news_item->setContent(ilUtil::stripSlashes($_POST["news_content"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString()));
			$this->news_item->setVisibility(ilUtil::stripSlashes($_POST["news_visibility"]));
			$this->news_item->setContentLong(ilUtil::stripSlashes($_POST["news_content_long"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString()));
			$this->prepareSaveNewsItem($this->news_item);
			$this->news_item->create();
		}
		else
		{
			$this->setFormEditMode(IL_FORM_RE_CREATE);
			return $this->outputFormNewsItem();
		}

	}

	/**
	* FORM NewsItem: Update NewsItem.
	*
	*/
	public function updateNewsItem()
	{
		include_once("./classes/class.ilObjAdvancedEditing.php");
		if ($this->checkInputNewsItem())
		{
			
			$this->news_item->setTitle(ilUtil::stripSlashes($_POST["news_title"]));
			$this->news_item->setContent(ilUtil::stripSlashes($_POST["news_content"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString()));
			$this->news_item->setVisibility(ilUtil::stripSlashes($_POST["news_visibility"]));
			$this->news_item->setContentLong(ilUtil::stripSlashes($_POST["news_content_long"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString()));
			$this->news_item->update();
		}
		else
		{
			$this->setFormEditMode(IL_FORM_RE_EDIT);
			return $this->outputFormNewsItem();
		}

	}

	/**
	* FORM NewsItem: Get current values for NewsItem form.
	*
	*/
	public function getValuesNewsItem()
	{
		$values = array();
		
		switch ($this->getFormEditMode())
		{
			case IL_FORM_CREATE:
				$values["Title"] = "";
				$values["Content"] = "";
				$values["Visibility"] = "users";
				$values["ContentLong"] = "";
				break;
				
			case IL_FORM_EDIT:
				$values["Title"] = $this->news_item->getTitle();
				$values["Content"] = $this->news_item->getContent();
				$values["Visibility"] = $this->news_item->getVisibility();
				$values["ContentLong"] = $this->news_item->getContentLong();
				break;
				
			case IL_FORM_RE_EDIT:
			case IL_FORM_RE_CREATE:
				$values["Title"] = ilUtil::stripSlashes($_POST["news_title"]);
				$values["Content"] = ilUtil::stripSlashes($_POST["news_content"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString());
				$values["Visibility"] = ilUtil::stripSlashes($_POST["news_visibility"]);
				$values["ContentLong"] = ilUtil::stripSlashes($_POST["news_content_long"]
				,true, ilObjAdvancedEditing::_getUsedHTMLTagsAsString());
				break;
		}
		
		return $values;

	}

	/**
	* FORM NewsItem: Check input.
	*
	*/
	public function checkInputNewsItem()
	{
		
		include_once("./Services/Utilities/classes/class.ilTypeCheck.php");
		$ilTypeCheck = new ilTypeCheck();
		
		$this->form_check["NewsItem"] = array();
		$this->form_check["NewsItem"]["Title"] =
			ilTypeCheck::check("varchar", $_POST["news_title"], true);
		$this->form_check["NewsItem"]["Content"] =
			ilTypeCheck::check("text", $_POST["news_content"], false);
		$this->form_check["NewsItem"]["Visibility"] =
			ilTypeCheck::check("enum", $_POST["news_visibility"], false);
		$this->form_check["NewsItem"]["ContentLong"] =
			ilTypeCheck::check("text", $_POST["news_content_long"], false);
		
		foreach($this->form_check["NewsItem"] as $prop_check)
		{
			if (!$prop_check["ok"])
			{
				return false;
			}
		}
		return true;

	}

	/**
	* FORM NewsItem: Prepare Saving of NewsItem.
	*
	* @param	object	$a_news_item	NewsItem object.
	*/
	public function prepareSaveNewsItem(&$a_news_item)
	{

	}

	/**
	* FORM NewsItem: Prepare form. (Can be overwritten in derived classes)
	*
	* @param	object	$a_form_gui	ilPropertyFormGUI instance.
	*/
	public function prepareFormNewsItem(&$a_form_gui)
	{

	}

	/**
	* BLOCK NewsForContext: Get block HTML.
	*
	*/
	public function getNewsForContextBlock()
	{
		global $lng;
		
		include_once("Services/News/classes/class.ilNewsForContextBlockGUI.php");
		$block_gui = new ilNewsForContextBlockGUI(get_class($this));
		$this->prepareBlockNewsForContext($block_gui);
		
		$news_item = new ilNewsItem();
		$this->prepareBlockQueryNewsForContext($news_item);
		$data = $news_item->queryNewsForContext();
		
		$block_gui->setTitle($lng->txt("news_block_news_for_context"));
		$block_gui->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		$block_gui->setData($data);
		
		return $block_gui->getHTML();

	}

	/**
	* BLOCK NewsForContext: Prepare block. (Can be overwritten in derived classes)
	*
	* @param	object	$a_block_gui	ilBlockGUI instance.
	*/
	public function prepareBlockNewsForContext(&$a_block_gui)
	{

	}

	/**
	* BLOCK NewsForContext: Prepare query for getting data for list block.
	*
	* @param	object	$a_news_item	NewsItem entity.
	*/
	public function prepareBlockQueryNewsForContext(&$a_news_item)
	{
		
		$a_news_item->setContextObjId($this->getContextObjId());
		$a_news_item->setContextObjType($this->getContextObjType());
		$a_news_item->setContextSubObjId($this->getContextSubObjId());
		$a_news_item->setContextSubObjType($this->getContextSubObjType());

	}

	/**
	* TABLE NewsForContext: Get table HTML.
	*
	*/
	public function getNewsForContextTable()
	{
		global $lng;
		
		include_once("Services/News/classes/class.ilNewsForContextTableGUI.php");
		$table_gui = new ilNewsForContextTableGUI($this, "getNewsForContextTable");
		
		$news_item = new ilNewsItem();
		$this->prepareTableQueryNewsForContext($news_item);
		$data = $news_item->queryNewsForContext();
		
		$table_gui->setTitle($lng->txt("news_table_news_for_context"));
		$table_gui->setRowTemplate("tpl.table_row_news_for_context.html", "Services/News");
		$table_gui->setData($data);
		$this->prepareTableNewsForContext($table_gui);
		
		return $table_gui->getHTML();

	}

	/**
	* TABLE NewsForContext: Prepare query for getting data for list table.
	*
	* @param	object	$a_news_item	NewsItem entity.
	*/
	public function prepareTableQueryNewsForContext(&$a_news_item)
	{
		
		$a_news_item->setContextObjId($this->getContextObjId());
		$a_news_item->setContextObjType($this->getContextObjType());
		$a_news_item->setContextSubObjId($this->getContextSubObjId());
		$a_news_item->setContextSubObjType($this->getContextSubObjType());

	}

	/**
	* TABLE NewsForContext: Prepare table before it is rendered. Please overwrite this in derived classes.
	*
	* @param	object	$a_table_gui	Table GUI object.
	*/
	public function prepareTableNewsForContext(&$a_table_gui)
	{

	}


}
?>
