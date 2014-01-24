<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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

	protected $enable_edit = 0;
	protected $context_obj_id;
	protected $context_obj_type;
	protected $context_sub_obj_id;
	protected $context_sub_obj_type;
	protected $form_edit_mode;

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
	* FORM NewsItem: Create NewsItem.
	*
	*/
	public function createNewsItem()
	{
		$this->initFormNewsItem(IL_FORM_CREATE);
		return $this->form_gui->getHtml();

	}

	/**
	* FORM NewsItem: Edit form.
	*
	*/
	public function editNewsItem()
	{
		$this->initFormNewsItem(IL_FORM_EDIT);
		$this->getValuesNewsItem();
		return $this->form_gui->getHtml();

	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	public function saveNewsItem()
	{
		$this->initFormNewsItem(IL_FORM_CREATE);
		if ($this->form_gui->checkInput())
		{
			$this->news_item = new ilNewsItem();
			$this->news_item->setTitle($this->form_gui->getInput("news_title"));
			$this->news_item->setContent($this->form_gui->getInput("news_content"));
			$this->news_item->setVisibility($this->form_gui->getInput("news_visibility"));
			$this->news_item->setContentLong($this->form_gui->getInput("news_content_long"));
			$this->prepareSaveNewsItem($this->news_item);
			$this->news_item->create();
			$this->exitSaveNewsItem();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->form_gui->getHtml();
		}

	}

	/**
	* FORM NewsItem: Update NewsItem.
	*
	*/
	public function updateNewsItem()
	{
		$this->initFormNewsItem(IL_FORM_EDIT);
		if ($this->form_gui->checkInput())
		{
			
			$this->news_item->setTitle($this->form_gui->getInput("news_title"));
			$this->news_item->setContent($this->form_gui->getInput("news_content"));
			$this->news_item->setVisibility($this->form_gui->getInput("news_visibility"));
			$this->news_item->setContentLong($this->form_gui->getInput("news_content_long"));
			$this->news_item->update();
			$this->exitUpdateNewsItem();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->form_gui->getHtml();
		}

	}

	/**
	* FORM NewsItem: Init form.
	*
	* @param	int	$a_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
	*/
	public function initFormNewsItem($a_mode)
	{
		global $lng;
		
		$lng->loadLanguageModule("news");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$this->form_gui = new ilPropertyFormGUI();
		
		
		// Property Title
		$text_input = new ilTextInputGUI($lng->txt("news_news_item_title"), "news_title");
		$text_input->setInfo("");
		$text_input->setRequired(true);
		$text_input->setMaxLength(200);
		$this->form_gui->addItem($text_input);
		
		// Property Content
		$text_area = new ilTextAreaInputGUI($lng->txt("news_news_item_content"), "news_content");
		$text_area->setInfo("");
		$text_area->setRequired(false);
		$this->form_gui->addItem($text_area);
		
		// Property Visibility
		$radio_group = new ilRadioGroupInputGUI($lng->txt("news_news_item_visibility"), "news_visibility");
		$radio_option = new ilRadioOption($lng->txt("news_visibility_users"), "users");
		$radio_group->addOption($radio_option);
		$radio_option = new ilRadioOption($lng->txt("news_visibility_public"), "public");
		$radio_group->addOption($radio_option);
		$radio_group->setInfo($lng->txt("news_news_item_visibility_info"));
		$radio_group->setRequired(false);
		$radio_group->setValue("users");
		$this->form_gui->addItem($radio_group);
		
		// Property ContentLong
		$text_area = new ilTextAreaInputGUI($lng->txt("news_news_item_content_long"), "news_content_long");
		$text_area->setInfo($lng->txt("news_news_item_content_long_info"));
		$text_area->setRequired(false);
		$text_area->setCols("40");
		$text_area->setRows("8");
		$text_area->setUseRte(true);
		$this->form_gui->addItem($text_area);
		

		// save and cancel commands
		if (in_array($a_mode, array(IL_FORM_CREATE,IL_FORM_RE_CREATE)))
		{
			$this->form_gui->addCommandButton("saveNewsItem", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelSaveNewsItem", $lng->txt("cancel"));
		}
		else
		{
			$this->form_gui->addCommandButton("updateNewsItem", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelUpdateNewsItem", $lng->txt("cancel"));
		}
		
		$this->form_gui->setTitle($lng->txt("news_news_item_head"));
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this));
		
		$this->prepareFormNewsItem($this->form_gui);

	}

	/**
	* FORM NewsItem: Get current values for NewsItem form.
	*
	*/
	public function getValuesNewsItem()
	{
		$values = array();
		
		$values["news_title"] = $this->news_item->getTitle();
		$values["news_content"] = $this->news_item->getContent();
		$values["news_visibility"] = $this->news_item->getVisibility();
		$values["news_content_long"] = $this->news_item->getContentLong();

		$this->form_gui->setValuesByArray($values);

	}

	/**
	* FORM NewsItem: Cancel save. (Can be overwritten in derived classes)
	*
	*/
	public function cancelSaveNewsItem()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}

	/**
	* FORM NewsItem: Cancel update. (Can be overwritten in derived classes)
	*
	*/
	public function cancelUpdateNewsItem()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}

	/**
	* FORM NewsItem: Exit save. (Can be overwritten in derived classes)
	*
	*/
	public function exitSaveNewsItem()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}

	/**
	* FORM NewsItem: Exit update. (Can be overwritten in derived classes)
	*
	*/
	public function exitUpdateNewsItem()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
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
