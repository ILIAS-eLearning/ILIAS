<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Single news timeline item
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesNews
 */
class ilNewsTimelineItemGUI implements ilTimelineItemInt
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilNewsItem
	 */
	protected $news_item;

	/**
	 * Constructor
	 *
	 * $param ilNewsItem $a_news_item
	 */
	protected function __construct(ilNewsItem $a_news_item)
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->setNewsItem($a_news_item);
	}

	/**
	 * Get instance
	 *
	 * @param ilNewsItem $a_news_item news item
	 * @return ilNewsTimelineItemGUI
	 */
	static function getInstance(ilNewsItem $a_news_item)
	{
		return new self($a_news_item);
	}


	/**
	 * Set news item
	 *
	 * @param ilNewsItem $a_val news item
	 */
	function setNewsItem(ilNewsItem $a_val)
	{
		$this->news_item = $a_val;
	}

	/**
	 * Get news item
	 *
	 * @return ilNewsItem news item
	 */
	function getNewsItem()
	{
		return $this->news_item;
	}

	/**
	 * @inheritdoc
	 */
	function render()
	{
		$i = $this->getNewsItem();
		$tpl = new ilTemplate("tpl.timeline_item.html", true, true, "Services/News");

		$tpl->setVariable("USER_IMAGE", ilObjUser::_getPersonalPicturePath($i->getUserId(), "xsmall"));
		$tpl->setVariable("TITLE", $i->getTitle());
		$tpl->setVariable("CONTENT", $i->getContent());

		// actions
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$list = new ilAdvancedSelectionListGUI();
		$list->setListTitle("");
		$list->setId("news_tl_act_".$i->getId());
		//$list->setSelectionHeaderClass("small");
		//$list->setItemLinkClass("xsmall");
		//$list->setLinksMode("il_ContainerItemCommand2");
		$list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$list->setUseImages(false);

		$list->addItem($this->lng->txt("edit"), "", "", "", "text?", "",
			"", false, "il.News.edit(".$i->getId().");");
		$tpl->setVariable("ACTIONS", $list->getHTML());


		return $tpl->get();
	}

}

?>