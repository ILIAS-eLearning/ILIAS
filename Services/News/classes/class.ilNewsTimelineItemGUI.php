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
     * @var \ilLikeGUI
     */
    protected $like_gui;

    /**
     * Constructor
     *
     * @param ilNewsItem $a_news_item
     * @param $a_news_ref_id
     * @param ilLikeGUI $a_like_gui
     */
    protected function __construct(ilNewsItem $a_news_item, $a_news_ref_id, \ilLikeGUI $a_like_gui)
    {
        global $DIC;

        $this->like_gui = $a_like_gui;

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
    public static function getInstance(ilNewsItem $a_news_item, $a_news_ref_id, \ilLikeGUI $a_like_gui)
    {
        return new self($a_news_item, $a_news_ref_id, $a_like_gui);
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
        if ($i->getMobId() > 0 && ilObject::_exists($i->getMobId())) {
            $media = $this->renderMedia($i);
            $tpl->setCurrentBlock("player");
            $tpl->setVariable("PLAYER", $media);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("USER_IMAGE", ilObjUser::_getPersonalPicturePath($i->getUserId(), "xsmall"));
        $tpl->setVariable(
            "TITLE",
            ilNewsItem::determineNewsTitle($i->getContextObjType(), $i->getTitle(), $i->getContentIsLangVar())
        );

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

    /**
     * Render media
     *
     * @param
     * @return
     */
    protected function renderMedia(ilNewsItem $i)
    {
        global $DIC;

        $media_path = $this->getMediaPath($i);
        $mime = ilObjMediaObject::getMimeType($media_path);

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        if (in_array($mime, array("image/jpeg", "image/svg+xml", "image/gif", "image/png"))) {
            $item_id = "il-news-modal-img-" . $i->getId();
            $title = basename($media_path);
            $image = $ui_renderer->render($ui_factory->image()->responsive($media_path, $title));

            $img_tpl = new ilTemplate("tpl.news_timeline_image_file.html", true, true, "Services/News");
            $img_tpl->setVariable("ITEM_ID", $item_id);
            $img_tpl->setVariable("IMAGE", $image);

            $html = $img_tpl->get();
        } elseif (in_array($mime, array("audio/mpeg", "audio/ogg", "video/mp4", "video/x-flv", "video/webm"))) {
            $mp = new ilMediaPlayerGUI();
            $mp->setFile($media_path);
            $mp->setDisplayHeight(200);
            $html = $mp->getMediaPlayerHtml();
        } else {
            // download?
            $html = "";
        }
        return $html;
    }

    /**
     * Render media
     *
     * @param ilNewsItem
     * @return string
     */
    protected function renderMediaModal(ilNewsItem $i)
    {
        global $DIC;

        $media_path = $this->getMediaPath($i);
        $mime = ilObjMediaObject::getMimeType($media_path);

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $modal_html = "";

        if (in_array($mime, array("image/jpeg", "image/svg+xml", "image/gif", "image/png"))) {
            $title = basename($media_path);
            $item_id = "il-news-modal-img-" . $i->getId();
            $image = $ui_renderer->render($ui_factory->image()->responsive($media_path, $title));
            $modal = ilModalGUI::getInstance();
            $modal->setId($item_id);
            $modal->setType(ilModalGUI::TYPE_LARGE);
            $modal->setBody($image);
            $modal->setHeading($title);
            $modal_html = $modal->getHTML();
        }
        return $modal_html;
    }


    /**
     * Render footer
     * @throws ilCtrlException
     */
    public function renderFooter()
    {
        $i = $this->getNewsItem();

        // like
        $this->ctrl->setParameterByClass("ilnewstimelinegui", "news_id", $i->getId());
        $this->like_gui->setObject(
            $i->getContextObjId(),
            $i->getContextObjType(),
            $i->getContextSubObjId(),
            $i->getContextSubObjType(),
            $i->getId()
        );
        $html = $this->ctrl->getHTML($this->like_gui);

        // comments
        $notes_obj_type = ($i->getContextSubObjType() == "")
            ? $i->getContextObjType()
            : $i->getContextSubObjType();
        $note_gui = new ilNoteGUI(
            $i->getContextObjId(),
            $i->getContextSubObjId(),
            $notes_obj_type,
            false,
            $i->getId()
        );
        $note_gui->setDefaultCommand("getWidget");

        //ilNoteGUI::getListCommentsJSCall($this->ajax_hash, $redraw_js)
        $html.= $this->ctrl->getHTML($note_gui);

        $this->ctrl->setParameterByClass("ilnewstimelinegui", "news_id", $_GET["news_id"]);

        return $html . $this->renderMediaModal($i);
    }

    /**
     * @param ilNewsItem $i
     * @return string
     */
    protected function getMediaPath(ilNewsItem $i)
    {
        $media_path = "";
        if ($i->getMobId() > 0) {
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
            $mob = new ilObjMediaObject($i->getMobId());
            $med = $mob->getMediaItem("Standard");
            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $media_path = $med->getLocation();
            } else {
                $media_path = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
            }
        }
        return $media_path;
    }
}
