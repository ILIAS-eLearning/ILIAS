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
     * @var ilObjectDefinition
     */
    protected $obj_def;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var bool
     */
    protected $user_edit_all;

    /**
     * Ref ID of news item
     *
     * @var int
     */
    protected $news_item_ref_id;

    /**
     * Ref id of timeline container
     * @var int
     */
    protected $ref_id;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     *
     * $param ilNewsItem $a_news_item
     */
    protected function __construct(ilNewsItem $a_news_item, $a_news_ref_id)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->setNewsItem($a_news_item);
        $this->user = $DIC->user();
        $this->obj_def = $DIC["objDefinition"];
        $this->news_item_ref_id = $a_news_ref_id;

        $this->ref_id = (int) $_GET["ref_id"];
    }

    /**
     * Get instance
     *
     * @param ilNewsItem $a_news_item news item
     * @return ilNewsTimelineItemGUI
     */
    public static function getInstance(ilNewsItem $a_news_item, $a_news_ref_id)
    {
        return new self($a_news_item, $a_news_ref_id);
    }


    /**
     * Set news item
     *
     * @param ilNewsItem $a_val news item
     */
    public function setNewsItem(ilNewsItem $a_val)
    {
        $this->news_item = $a_val;
    }

    /**
     * Get news item
     *
     * @return ilNewsItem news item
     */
    public function getNewsItem()
    {
        return $this->news_item;
    }
    
    /**
     * Set user can edit other users postings
     *
     * @param bool $a_val user can edit all postings
     */
    public function setUserEditAll($a_val)
    {
        $this->user_edit_all = $a_val;
    }
    
    /**
     * Get user can edit other users postings
     *
     * @return bool user can edit all postings
     */
    public function getUserEditAll()
    {
        return $this->user_edit_all;
    }

    /**
     * @inheritdoc
     */
    public function getDateTime()
    {
        $i = $this->getNewsItem();
        return new ilDateTime($i->getCreationDate(), IL_CAL_DATETIME);
    }


    /**
     * @inheritdoc
     */
    public function render()
    {
        $i = $this->getNewsItem();
        $tpl = new ilTemplate("tpl.timeline_item.html", true, true, "Services/News");

        include_once("./Services/News/classes/class.ilNewsRendererFactory.php");
        $news_renderer = ilNewsRendererFactory::getRenderer($i->getContextObjType());
        $news_renderer->setLanguage($this->lng->getLangKey());
        $news_renderer->setNewsItem($i, $this->news_item_ref_id);

        $obj_id = $i->getContextObjId();

        // edited?
        if ($i->getCreationDate() != $i->getUpdateDate()) {
            $tpl->setCurrentBlock("edited");
            $update_date = new ilDateTime($i->getUpdateDate(), IL_CAL_DATETIME);
            $tpl->setVariable("TXT_EDITED", $this->lng->txt("cont_news_edited"));
            if ($i->getUpdateUserId() > 0 && ($i->getUpdateUserId() != $i->getUserId())) {
                include_once("./Services/User/classes/class.ilUserUtil.php");
                $tpl->setVariable("TXT_USR_EDITED", ilUserUtil::getNamePresentation(
                    $i->getUpdateUserId(),
                    false,
                    true,
                    $this->ctrl->getLinkTargetByClass("ilnewstimelinegui")
                ) . " - ");
            }
            include_once("./Services/Calendar/classes/class.ilDatePresentation.php");
            $tpl->setVariable("TIME_EDITED", ilDatePresentation::formatDate($update_date));
            $tpl->parseCurrentBlock();
        }


        // context object link
        include_once("./Services/Link/classes/class.ilLink.php");
        if ($this->news_item_ref_id > 0 && $this->ref_id != $this->news_item_ref_id) {
            $tpl->setCurrentBlock("object");
            $tpl->setVariable("OBJ_TITLE", ilObject::_lookupTitle($obj_id));
            $tpl->setVariable("OBJ_IMG", ilObject::_getIcon($obj_id));
            $tpl->setVariable("OBJ_HREF", $news_renderer->getObjectLink());
            $tpl->parseCurrentBlock();
        }

        // media
        if ($i->getContentType() == NEWS_AUDIO &&
            $i->getMobId() > 0 && ilObject::_exists($i->getMobId())) {
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
            $mob = new ilObjMediaObject($i->getMobId());
            $med = $mob->getMediaItem("Standard");
            $mpl = new ilMediaPlayerGUI("news_pl_" . $i->getMobId());
            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $mpl->setFile($med->getLocation());
            } else {
                $mpl->setFile(ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation());
            }
            $mpl->setDisplayHeight($med->getHeight());
            //$mpl->setDisplayWidth("100%");
            //$mpl->setDisplayHeight("320");
            $tpl->setCurrentBlock("player");
            $tpl->setVariable(
                "PLAYER",
                $mpl->getMp3PlayerHtml()
            );
            $tpl->parseCurrentBlock();
        }


        $tpl->setVariable("USER_IMAGE", ilObjUser::_getPersonalPicturePath($i->getUserId(), "xsmall"));
        if (!$i->getContentIsLangVar()) {
            $tpl->setVariable("TITLE", $i->getTitle());
        } else {
            $tpl->setVariable("TITLE", $this->lng->txt($i->getTitle()));
        }

        // content
        $tpl->setVariable("CONTENT", $news_renderer->getTimelineContent());

        include_once("./Services/User/classes/class.ilUserUtil.php");
        $tpl->setVariable("TXT_USR", ilUserUtil::getNamePresentation(
            $i->getUserId(),
            false,
            true,
            $this->ctrl->getLinkTargetByClass("ilnewstimelinegui")
        ));

        include_once("./Services/Calendar/classes/class.ilDatePresentation.php");
        $tpl->setVariable("TIME", ilDatePresentation::formatDate($this->getDateTime()));

        // actions
        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle("");
        $list->setId("news_tl_act_" . $i->getId());
        //$list->setSelectionHeaderClass("small");
        //$list->setItemLinkClass("xsmall");
        //$list->setLinksMode("il_ContainerItemCommand2");
        $list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $list->setUseImages(false);

        if ($i->getPriority() == 1 && ($i->getUserId() == $this->user->getId() || $this->getUserEditAll())) {
            $list->addItem(
                $this->lng->txt("edit"),
                "",
                "",
                "",
                "",
                "",
                "",
                false,
                "il.News.edit(" . $i->getId() . ");"
            );
            $list->addItem(
                $this->lng->txt("delete"),
                "",
                "",
                "",
                "",
                "",
                "",
                false,
                "il.News.delete(" . $i->getId() . ");"
            );
        }

        $news_renderer->addTimelineActions($list);

        $tpl->setVariable("ACTIONS", $list->getHTML());


        return $tpl->get();
    }
}
