<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/interfaces/interface.ilNewsRendererGUI.php");
/**
 * Default renderer
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
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    /**
     * @inheritdoc
     */
    public function setNewsItem(ilNewsItem $a_news_item, $a_news_ref_id)
    {
        $this->news_item = $a_news_item;
        $this->news_ref_id = $a_news_ref_id;
    }

    /**
     * Get news item
     *
     * @return ilNewsItem
     */
    public function getNewsItem()
    {
        return $this->news_item;
    }

    /**
     * Get news ref id
     *
     * @return int ref id
     */
    public function getNewsRefId()
    {
        return $this->news_ref_id;
    }


    /**
     * @inheritdoc
     */
    public function setLanguage($a_lang_key)
    {
        $this->lng_key = $a_lang_key;
    }


    /**
     * @inheritdoc
     */
    public function getTimelineContent()
    {
        return $this->getDetailContent();
    }

    /**
     * @inheritdoc
     */
    public function getDetailContent()
    {
        if ($this->news_item->getContentTextIsLangVar()) {
            $this->lng->loadLanguageModule($this->news_item->getContextObjType());
            return ilNewsItem::determineNewsContent(
                $this->news_item->getContextObjType(),
                $this->news_item->getContent(),
                $this->news_item->getContentTextIsLangVar()
            );
        }

        $content = $this->makeClickable($this->news_item->getContent());
        if (!$this->news_item->getContentHtml()) {
            $content = "<p>" . nl2br($content) . "</p>";
        }
        $content.= $this->news_item->getContentLong();

        return $content;
    }

    /**
     * Make clickable
     *
     * @param
     * @return
     */
    public function makeClickable($a_str)
    {
        // this fixes bug 8744.
        // If the string already contains a tags our makeClickable does not work
        if (is_int(strpos($a_str, "</a>")) && is_int(strpos($a_str, "<a"))) {
            return $a_str;
        }

        return ilUtil::makeClickable($a_str);
    }


    /**
     * @param ilAdvancedSelectionListGUI $list
     */
    public function addTimelineActions(ilAdvancedSelectionListGUI $list)
    {
    }

    /**
     * Get object link
     *
     * @return string link href url
     */
    public function getObjectLink()
    {
        include_once("./Services/Link/classes/class.ilLink.php");
        return ilLink::_getLink($this->getNewsRefId());
    }
}
