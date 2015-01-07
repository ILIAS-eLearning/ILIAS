<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsItem.php");

define("IL_FORM_EDIT", 0);
define("IL_FORM_CREATE", 1);
define("IL_FORM_RE_EDIT", 2);
define("IL_FORM_RE_CREATE", 3);

/**
 * User Interface for NewsItem entities.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesNews
 */
class ilNewsItemGUI
{
	protected $enable_edit = 0;
	protected $context_obj_id;
	protected $context_obj_type;
	protected $context_sub_obj_id;
	protected $context_sub_obj_type;
	protected $form_edit_mode;


	/**
	 * Constructor
	 */
	function __construct()
	{
		global $ilCtrl, $lng;
		
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

		$lng->loadLanguageModule("news");

		$ilCtrl->saveParameter($this, "add_mode");
	}

	/**
	 * Get html
	 *
	 * @return string	html
	 */
	function getHTML()
	{
		global $lng, $ilCtrl;
		
		$lng->LoadLanguageModule("news");
		
		return $this->getNewsForContextBlock();
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
	 * FORM NewsItem: Init form.
	 *
	 * @param	int	$a_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
	 */
	public function initFormNewsItem($a_mode)
	{
		global $lng, $ilTabs;

		$ilTabs->clearTargets();
		//$this->setTabs();

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

		$news_set = new ilSetting("news");
		if (!$news_set->get("enable_rss_for_internal"))
		{
			$this->form_gui->removeItemByPostVar("news_visibility");
		}
		else
		{
			$nv = $this->form_gui->getItemByPostVar("news_visibility");
			if (is_object($nv))
			{
				$nv->setValue(ilNewsItem::_getDefaultVisibilityForRefId($_GET["ref_id"]));
			}
		}

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
	 * FORM NewsItem: Save NewsItem.
	 *
	 */
	function saveNewsItem()
	{
		global $ilUser;

		if (!$this->getEnableEdit())
		{
			return;
		}

		$this->initFormNewsItem(IL_FORM_CREATE);
		if ($this->form_gui->checkInput())
		{
			$this->news_item = new ilNewsItem();
			$this->news_item->setTitle($this->form_gui->getInput("news_title"));
			$this->news_item->setContent($this->form_gui->getInput("news_content"));
			$this->news_item->setVisibility($this->form_gui->getInput("news_visibility"));
			$this->news_item->setContentLong($this->form_gui->getInput("news_content_long"));

// changed
			//$this->news_item->setContextObjId($this->ctrl->getContextObjId());
			//$this->news_item->setContextObjType($this->ctrl->getContextObjType());
			$this->news_item->setContextObjId($this->getContextObjId());
			$this->news_item->setContextObjType($this->getContextObjType());
			$this->news_item->setContextSubObjId($this->getContextSubObjId());
			$this->news_item->setContextSubObjType($this->getContextSubObjType());
			$this->news_item->setUserId($ilUser->getId());

			$news_set = new ilSetting("news");
			if (!$news_set->get("enable_rss_for_internal"))
			{
				$this->news_item->setVisibility("users");
			}

			$this->news_item->create();
			$this->exitSaveNewsItem();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->form_gui->getHtml();
		}

	}

	function exitSaveNewsItem()
	{
		global $ilCtrl;

		if ($_GET["add_mode"] == "block")
		{
			$ilCtrl->returnToParent($this);
		}
		else
		{
			$ilCtrl->redirect($this, "editNews");
		}
	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	function updateNewsItem()
	{
		if (!$this->getEnableEdit())
		{
			return;
		}

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

	function exitUpdateNewsItem()
	{
		global $ilCtrl;

		$ilCtrl->redirect($this, "editNews");
	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	function cancelUpdateNewsItem()
	{
		return $this->editNews();
	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	function cancelSaveNewsItem()
	{
		global $ilCtrl;

		if ($_GET["add_mode"] == "block")
		{
			$ilCtrl->returnToParent($this);
		}
		else
		{
			return $this->editNews();
		}
	}

	/**
	 * Edit news
	 *
	 * @return html
	 */
	function editNews()
	{
		global $ilTabs, $ilToolbar, $lng, $ilCtrl;

		$this->setTabs();

		$ilToolbar->addButton($lng->txt("news_add_news"),
			$ilCtrl->getLinkTarget($this, "createNewsItem"));

		if (!$this->getEnableEdit())
		{
			return;
		}
		return $this->getNewsForContextTable();
	}

	/**
	 * Cancel update
	 */
	function cancelUpdate()
	{
		return $this->editNews();
	}

	/**
	* Confirmation Screen.
	*/
	function confirmDeletionNewsItems()
	{
		global $ilCtrl, $lng, $ilTabs;

		if (!$this->getEnableEdit())
		{
			return;
		}

		// check whether at least one item is selected
		if (count($_POST["news_id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->editNews();
		}

		$ilTabs->clearTargets();

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($ilCtrl->getFormAction($this, "deleteNewsItems"));
		$c_gui->setHeaderText($lng->txt("info_delete_sure"));
		$c_gui->setCancel($lng->txt("cancel"), "editNews");
		$c_gui->setConfirm($lng->txt("confirm"), "deleteNewsItems");

		// add items to delete
		foreach($_POST["news_id"] as $news_id)
		{
			$news = new ilNewsItem($news_id);
			$c_gui->addItem("news_id[]", $news_id, $news->getTitle());
		}

		return $c_gui->getHTML();
	}

	/**
	* Delete news items.
	*/
	function deleteNewsItems()
	{
		if (!$this->getEnableEdit())
		{
			return;
		}
		// delete all selected news items
		foreach($_POST["news_id"] as $news_id)
		{
			$news = new ilNewsItem($news_id);
			$news->delete();
		}

		return $this->editNews();
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

		$block_gui->setParentClass("ilinfoscreengui");
		$block_gui->setParentCmd("showSummary");
		$block_gui->setEnableEdit($this->getEnableEdit());


		$news_item = new ilNewsItem();

// changed
		//$news_item->setContextObjId($this->ctrl->getContextObjId());
		//$news_item->setContextObjType($this->ctrl->getContextObjType());
		$news_item->setContextObjId($this->getContextObjId());
		$news_item->setContextObjType($this->getContextObjType());
		$news_item->setContextSubObjId($this->getContextSubObjId());
		$news_item->setContextSubObjType($this->getContextSubObjType());

		$data = $news_item->queryNewsForContext();

		$block_gui->setTitle($lng->txt("news_block_news_for_context"));
		$block_gui->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		$block_gui->setData($data);

		return $block_gui->getHTML();

	}


	/**
	 * TABLE NewsForContext: Get table HTML.
	 *
	 */
	public function getNewsForContextTable()
	{
		global $lng;

		$news_item = new ilNewsItem();
		$news_item->setContextObjId($this->getContextObjId());
		$news_item->setContextObjType($this->getContextObjType());
		$news_item->setContextSubObjId($this->getContextSubObjId());
		$news_item->setContextSubObjType($this->getContextSubObjType());

		$perm_ref_id = 0;
		if (in_array($this->getContextObjType(), array("cat", "grp", "crs", "root")))
		{
			$data = $news_item->getNewsForRefId($_GET["ref_id"], false, false,
				0, true, false, true, true);
		}
		else
		{
			$perm_ref_id = $_GET["ref_id"];
			if ($this->getContextSubObjId() > 0)
			{
				$data = $news_item->queryNewsForContext(false, 0,
					"", true, true);
			}
			else
			{
				$data = $news_item->queryNewsForContext();
			}
		}

		include_once("Services/News/classes/class.ilNewsForContextTableGUI.php");
		$table_gui = new ilNewsForContextTableGUI($this, "getNewsForContextTable", $perm_ref_id);

		$table_gui->setTitle($lng->txt("news_table_news_for_context"));
		$table_gui->setRowTemplate("tpl.table_row_news_for_context.html", "Services/News");
		$table_gui->setData($data);

		$table_gui->setDefaultOrderField("creation_date");
		$table_gui->setDefaultOrderDirection("desc");
		$table_gui->addMultiCommand("confirmDeletionNewsItems", $lng->txt("delete"));
		$table_gui->setTitle($lng->txt("news"));
		$table_gui->setSelectAllCheckbox("news_id");


		return $table_gui->getHTML();

	}
	
	/**
	 * Set tabs
	 *
	 * @param
	 * @return
	 */
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $lng;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getParentReturnByClass("ilnewsitemgui"));
	}
}
