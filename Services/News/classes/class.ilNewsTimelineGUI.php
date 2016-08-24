<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Timeline for news
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesNews
 */
class ilNewsTimelineGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var int
	 */
	protected static $items_per_load = 10;

	/**
	 * Constructor
	 *
	 * @param int $a_ref_id reference id
	 */
	protected function __construct($a_ref_id, $a_include_auto_entries)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->ref_id = $a_ref_id;
		$this->lng = $DIC->language();
		$this->toolbar = $DIC->toolbar();
		$this->user = $DIC->user();
		$this->include_auto_entries = $a_include_auto_entries;
		$this->access = $DIC->access();
	}

	/**
	 * Get instance
	 *
	 * @param int $a_ref_id reference id
	 * @return ilNewsTimelineGUI
	 */
	static function getInstance($a_ref_id, $a_include_auto_entries)
	{
		return new self($a_ref_id, $a_include_auto_entries);
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("show");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("show", "save", "update", "loadMore")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Show
	 *
	 * @param
	 * @return
	 */
	function show()
	{
		// toolbar
		if ($this->access->checkAccess("news_add_news", "", $this->ref_id))
		{
			$b = ilLinkButton::getInstance();
			$b->setCaption('add');
			$b->setOnClick("return il.News.create();");
			$b->setPrimary(true);
			$this->toolbar->addButtonInstance($b);
		}

		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news_item = new ilNewsItem();
		$news_item->setContextObjId($this->ctrl->getContextObjId());
		$news_item->setContextObjType($this->ctrl->getContextObjType());

		$news_data = $news_item->getNewsForRefId($this->ref_id, false, false,
			0, true, false,	!$this->include_auto_entries, false, null, self::$items_per_load);

		include_once("./Services/News/Timeline/classes/class.ilTimelineGUI.php");
		include_once("./Services/News/classes/class.ilNewsTimelineItemGUI.php");
		$timeline = ilTimelineGUI::getInstance();

		$js_items = array();
		foreach ($news_data as $d)
		{
			$news_item = new ilNewsItem($d["id"]);
			$item = ilNewsTimelineItemGUI::getInstance($news_item, $d["ref_id"]);
			$timeline->addItem($item);
			$js_items[$d["id"]] = array(
				"id" => $d["id"],
				"user_id" => $d["user_id"],
				"title" => $d["title"],
				"content" => $d["content"].$d["content_long"],
				"content_long" => "",
				"priority" => $d["priority"],
				"content_type" => $d["content_type"]
			);
		}

		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
		$this->tpl->addOnloadCode("il.News.setItems(".ilJsonUtil::encode($js_items).");");
		$this->tpl->addOnloadCode("il.News.setAjaxUrl('".$this->ctrl->getLinkTarget($this, "", "", true)."');");

		$ttpl = new ilTemplate("tpl.news_timeline.html", true, true, "Services/News");
		$ttpl->setVariable("NEWS", $timeline->render());
		$ttpl->setVariable("EDIT_MODAL", $this->getEditModal());
		$ttpl->setVariable("LOADER", ilUtil::getImagePath("loader.svg"));

		$this->tpl->setContent($ttpl->get());

		$this->lng->toJS("create");
		$this->lng->toJS("edit");
		$this->lng->toJS("update");
		$this->lng->toJS("save");

		$this->tpl->addJavaScript("./Services/News/js/News.js");
		include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
		ilMediaPlayerGUI::initJavascript($this->tpl);
	}

	/**
	 * Load more
	 *
	 * @param
	 * @return
	 */
	function loadMore()
	{
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news_item = new ilNewsItem();
		$news_item->setContextObjId($this->ctrl->getContextObjId());
		$news_item->setContextObjType($this->ctrl->getContextObjType());

		$excluded = $_POST["rendered_news"];

		$news_data = $news_item->getNewsForRefId($this->ref_id, false, false,
			0, true, false,	!$this->include_auto_entries, false, null, self::$items_per_load, $excluded);

		include_once("./Services/News/Timeline/classes/class.ilTimelineGUI.php");
		include_once("./Services/News/classes/class.ilNewsTimelineItemGUI.php");
		$timeline = ilTimelineGUI::getInstance();

		$js_items = array();
		foreach ($news_data as $d)
		{
			$news_item = new ilNewsItem($d["id"]);
			$item = ilNewsTimelineItemGUI::getInstance($news_item, $d["ref_id"]);
			$timeline->addItem($item);
			$js_items[$d["id"]] = array(
				"id" => $d["id"],
				"user_id" => $d["user_id"],
				"title" => $d["title"],
				"content" => $d["content"].$d["content_long"],
				"content_long" => "",
				"priority" => $d["priority"],
				"content_type" => $d["content_type"]
			);
		}

		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
		$obj = new stdClass();
		$obj->data = $js_items;
		$obj->html = $timeline->render(true);

		echo ilJsonUtil::encode($obj);
		exit;
	}


	/**
	 * Save (ajax)
	 */
	function save()
	{
		include_once("./Services/News/classes/class.ilNewsItemGUI.php");
		$form = ilNewsItemGUI::getEditForm(IL_FORM_CREATE, $this->ref_id);
		if ($form->checkInput())
		{
			$news_item = new ilNewsItem();
			$news_item->setTitle($form->getInput("news_title"));
			$news_item->setContent($form->getInput("news_content"));
			$news_item->setVisibility($form->getInput("news_visibility"));
			include_once("./Services/News/classes/class.ilNewsItemGUI.php");
			if (ilNewsItemGUI::isRteActivated())
			{
				$news_item->setContentHtml(true);
			}
			//$news_item->setContentLong($form->getInput("news_content_long"));
			$news_item->setContentLong("");

			$obj_id = ilObject::_lookupObjectId($this->ref_id);
			$obj_type = ilObject::_lookupType($obj_id);
			$news_item->setContextObjId($obj_id);
			$news_item->setContextObjType($obj_type);
			$news_item->setUserId($this->user->getId());

			$news_set = new ilSetting("news");
			if (!$news_set->get("enable_rss_for_internal"))
			{
				$news_item->setVisibility("users");
			}

			$news_item->create();
		}
		exit;
	}

	/**
	 * Update (ajax)
	 */
	function update()
	{
		include_once("./Services/News/classes/class.ilNewsItemGUI.php");
		$form = ilNewsItemGUI::getEditForm(IL_FORM_EDIT, $this->ref_id);
		if ($form->checkInput())
		{
			$news_item = new ilNewsItem((int) $_POST["id"]);
			$news_item->setTitle($form->getInput("news_title"));
			$news_item->setContent($form->getInput("news_content"));
			$news_item->setVisibility($form->getInput("news_visibility"));
			//$news_item->setContentLong($form->getInput("news_content_long"));
			include_once("./Services/News/classes/class.ilNewsItemGUI.php");
			if (ilNewsItemGUI::isRteActivated())
			{
				$news_item->setContentHtml(true);
			}
			$news_item->setContentLong("");

			$obj_id = ilObject::_lookupObjectId($this->ref_id);

			if ($news_item->getContextObjId() == $obj_id &&
				($news_item->getUserId() == $this->user->getId()
				))
			{
				$news_item->update();
			}

		}
		exit;
	}

	/**
	 * Get edit modal
	 *
	 * @return string modal html
	 */
	protected function getEditModal()
	{
		include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
		$modal = ilModalGUI::getInstance();
		$modal->setHeading($this->lng->txt("edit"));
		$modal->setId("ilNewsEditModal");
		$modal->setType(ilModalGUI::TYPE_LARGE);

		include_once("./Services/News/classes/class.ilNewsItemGUI.php");
		$form = ilNewsItemGUI::getEditForm(IL_FORM_EDIT, $this->ref_id);
		$form->setShowTopButtons(false);
		$modal->setBody($form->getHTML());

		return $modal->getHTML();

	}

}
?>