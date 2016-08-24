<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News render interface
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesNews
 */
interface ilNewsRendererGUI
{
	/**
	 * Constructor
	 */
	function __construct();

	/**
	 * Language key
	 *
	 * @param $i ilNewsItem news item
	 */
	public function setLanguage($lang_key);

	/**
	 * Set news item
	 *
	 * @param ilNewsItem $a_news_item
	 * @param int $a_news_ref_id
	 */
	function setNewsItem(ilNewsItem $a_news_item, $a_news_ref_id);

	/**
	 * Render content for timeline
	 *
	 * @param $i ilNewsItem news item
	 * @return string html
	 */
	public function getTimelineContent();

	/**
	 * @param ilAdvancedSelectionListGUI $list
	 */
	public function addTimelineActions(ilAdvancedSelectionListGUI $list);

}

?>