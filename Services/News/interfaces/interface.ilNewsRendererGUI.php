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
    public function __construct();

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
    public function setNewsItem(ilNewsItem $a_news_item, $a_news_ref_id);

    /**
     * Render content for timeline
     *
     * @return string html
     */
    public function getTimelineContent();

    /**
     * Render content for detail view
     *
     * @return string html
     */
    public function getDetailContent();

    /**
     * @param ilAdvancedSelectionListGUI $list
     */
    public function addTimelineActions(ilAdvancedSelectionListGUI $list);

    /**
     * Get link href for object link
     *
     * @return string link href url
     */
    public function getObjectLink();
}
