<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Default renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsDefaultRendererGUI implements ilNewsRendererGUI
{
    protected \ILIAS\Refinery\Factory $refinery;
    protected string $lng_key;
    protected ilCtrl$ctrl;
    protected ilLanguage $lng;
    protected ilNewsItem $news_item;
    protected int $news_ref_id;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->refinery = $DIC->refinery();
    }

    public function setNewsItem(
        ilNewsItem $a_news_item,
        int $a_news_ref_id
    ) : void {
        $this->news_item = $a_news_item;
        $this->news_ref_id = $a_news_ref_id;
    }

    public function getNewsItem() : ilNewsItem
    {
        return $this->news_item;
    }

    public function getNewsRefId() : int
    {
        return $this->news_ref_id;
    }

    public function setLanguage(string $lang_key) : void
    {
        $this->lng_key = $lang_key;
    }

    public function getTimelineContent() : string
    {
        return $this->getDetailContent();
    }

    public function getDetailContent() : string
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
        $content .= $this->news_item->getContentLong();

        return $content;
    }

    public function makeClickable(string $a_str) : string
    {
        // this fixes bug 8744.
        // If the string already contains a tags our makeClickable does not work
        if (is_int(strpos($a_str, "</a>")) && is_int(strpos($a_str, "<a"))) {
            return $a_str;
        }

        return $this->refinery->string()->makeClickable()->transform($a_str);
    }

    public function addTimelineActions(ilAdvancedSelectionListGUI $list) : void
    {
    }

    public function getObjectLink() : string
    {
        return ilLink::_getLink($this->getNewsRefId());
    }
}
