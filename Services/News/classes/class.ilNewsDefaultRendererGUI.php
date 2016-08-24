<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/interfaces/interface.ilNewsRendererGUI.php");
/**
 *  Default renderer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesNews
 */
class ilNewsDefaultRendererGUI implements ilNewsRendererGUI
{
	protected $lng_key;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilNewsItem
	 */
	protected $news_item;

	/**
	 * @var int
	 */
	protected $news_ref_id;

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
	}

	/**
	 * @inheritdoc
	 */
	function setNewsItem(ilNewsItem $a_news_item, $a_news_ref_id)
	{
		$this->news_item = $a_news_item;
		$this->news_ref_id = $a_news_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	function setLanguage($a_lang_key)
	{
		$this->lng_key = $a_lang_key;
	}


	/**
	 * @inheritdoc
	 */
	public function getTimelineContent()
	{
		return "<p>".nl2br($this->news_item->getContent())."</p>".$this->news_item->getContentLong();
	}

	/**
	 * @param ilAdvancedSelectionListGUI $list
	 */
	public function addTimelineActions(ilAdvancedSelectionListGUI $list)
	{

	}

}

?>