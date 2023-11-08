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

use ILIAS\News\StandardGUIRequest;

/**
 * Single news timeline item
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsTimelineItemGUI implements ilTimelineItemInt
{
    protected \ILIAS\News\InternalGUIService $gui;
    protected \ILIAS\Notes\Service $notes;
    protected ilLanguage $lng;
    protected ilNewsItem $news_item;
    protected ilObjectDefinition $obj_def;
    protected ilObjUser $user;
    protected bool $user_edit_all;
    protected int $news_item_ref_id;
    protected int $ref_id;
    protected ilCtrl $ctrl;
    protected ilLikeGUI $like_gui;
    protected StandardGUIRequest $std_request;

    protected function __construct(
        ilNewsItem $a_news_item,
        int $a_news_ref_id,
        ilLikeGUI $a_like_gui
    ) {
        global $DIC;

        $this->like_gui = $a_like_gui;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->setNewsItem($a_news_item);
        $this->user = $DIC->user();
        $this->obj_def = $DIC["objDefinition"];
        $this->news_item_ref_id = $a_news_ref_id;

        $this->std_request = $DIC->news()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->ref_id = $this->std_request->getRefId();
        $this->gui = $DIC->news()
            ->internal()
            ->gui();
        $this->notes = $DIC->notes();
    }

    public static function getInstance(
        ilNewsItem $a_news_item,
        int $a_news_ref_id,
        ilLikeGUI $a_like_gui
    ): self {
        return new self($a_news_item, $a_news_ref_id, $a_like_gui);
    }

    public function setNewsItem(ilNewsItem $a_val): void
    {
        $this->news_item = $a_val;
    }

    public function getNewsItem(): ilNewsItem
    {
        return $this->news_item;
    }

    /**
     * Set user can edit other users postings
     */
    public function setUserEditAll(bool $a_val): void
    {
        $this->user_edit_all = $a_val;
    }

    /**
     * Get user can edit other users postings
     */
    public function getUserEditAll(): bool
    {
        return $this->user_edit_all;
    }

    public function getDateTime(): ilDateTime
    {
        $i = $this->getNewsItem();
        return new ilDateTime($i->getCreationDate(), IL_CAL_DATETIME);
    }

    public function render(): string
    {
        $i = $this->getNewsItem();
        $tpl = new ilTemplate("tpl.timeline_item.html", true, true, "components/ILIAS/News");
        $ui_factory = $this->gui->ui()->factory();
        $ui_renderer = $this->gui->ui()->renderer();

        $news_renderer = ilNewsRendererFactory::getRenderer($i->getContextObjType());
        $news_renderer->setLanguage($this->lng->getLangKey());
        $news_renderer->setNewsItem($i, $this->news_item_ref_id);

        $obj_id = $i->getContextObjId();

        // edited?
        if ($i->getCreationDate() !== $i->getUpdateDate()) {
            $tpl->setCurrentBlock("edited");
            $update_date = new ilDateTime($i->getUpdateDate(), IL_CAL_DATETIME);
            $tpl->setVariable("TXT_EDITED", $this->lng->txt("cont_news_edited"));
            if ($i->getUpdateUserId() > 0 && ($i->getUpdateUserId() !== $i->getUserId())) {
                $tpl->setVariable("TXT_USR_EDITED", ilUserUtil::getNamePresentation(
                    $i->getUpdateUserId(),
                    false,
                    true,
                    $this->ctrl->getLinkTargetByClass("ilnewstimelinegui")
                ) . " - ");
            }
            $tpl->setVariable("TIME_EDITED", ilDatePresentation::formatDate($update_date));
            $tpl->parseCurrentBlock();
        }

        // context object link
        if ($this->news_item_ref_id > 0 && $this->ref_id !== $this->news_item_ref_id) {
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

        $p = $this->gui->profile();
        $tpl->setVariable("USER_AVATAR", $this->gui->ui()->renderer()->render(
            $p->getAvatar($i->getUserId())
        ));
        $tpl->setVariable(
            "TITLE",
            ilNewsItem::determineNewsTitle($i->getContextObjType(), $i->getTitle(), $i->getContentIsLangVar())
        );

        // content
        $tpl->setVariable("CONTENT", $news_renderer->getTimelineContent());

        $tpl->setVariable("TXT_USR", $p->getNamePresentation(
            $i->getUserId(),
            true,
            $this->ctrl->getLinkTargetByClass("ilnewstimelinegui")
        ));

        $tpl->setVariable("TIME", ilDatePresentation::formatDate($this->getDateTime()));

        // actions
        $actions = [];

        if ($i->getPriority() === 1 && ($i->getUserId() === $this->user->getId() || $this->getUserEditAll())) {
            if (!$news_renderer->preventEditing()) {
                $actions[] = $ui_factory->button()->shy(
                    $this->lng->txt("edit"),
                    ""
                )->withOnLoadCode(static function ($id) use ($i) {
                    return "document.getElementById('$id').addEventListener('click', () => {il.News.edit(" . $i->getId() . ");});";
                });
                $actions[] = $ui_factory->button()->shy(
                    $this->lng->txt("delete"),
                    ""
                )->withOnLoadCode(static function ($id) use ($i) {
                    return "document.getElementById('$id').addEventListener('click', () => {il.News.delete(" . $i->getId() . ");});";
                });
            }
        }
        foreach ($news_renderer->getTimelineActions() as $action) {
            $actions[] = $action;
        }
        $dd = $ui_factory->dropdown()->standard($actions);
        $tpl->setVariable("ACTIONS", $ui_renderer->render($dd));

        return $tpl->get();
    }

    protected function renderMedia(ilNewsItem $i): string
    {
        global $DIC;

        $media_path = $this->getMediaPath($i);
        $mime = ilObjMediaObject::getMimeType($media_path);

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        if (in_array($mime, ["image/jpeg", "image/svg+xml", "image/gif", "image/png"])) {
            $item_id = "il-news-modal-img-" . $i->getId();
            $title = basename($media_path);
            $image = $ui_renderer->render($ui_factory->image()->responsive($media_path, $title));

            $img_tpl = new ilTemplate("tpl.news_timeline_image_file.html", true, true, "components/ILIAS/News");
            $img_tpl->setVariable("ITEM_ID", $item_id);
            $img_tpl->setVariable("IMAGE", $image);

            $html = $img_tpl->get();
        } elseif (in_array($mime, ["video/mp4", "video/youtube", "video/vimeo"])) {
            $video = $ui_factory->player()->video($media_path);
            $html = $ui_renderer->render($video);
        } elseif (in_array($mime, ["audio/mpeg"])) {
            $audio = $ui_factory->player()->audio($media_path);
            $html = $ui_renderer->render($audio);
        } elseif (in_array($mime, ["application/pdf"])) {
            $this->ctrl->setParameterByClass("ilnewstimelinegui", "news_id", $i->getId());
            $link = $ui_factory->link()->standard(
                basename($media_path),
                $this->ctrl->getLinkTargetByClass("ilnewstimelinegui", "downloadMob")
            );
            $html = $ui_renderer->render($link);
            $this->ctrl->setParameterByClass("ilnewstimelinegui", "news_id", null);
        } else {
            $html = "";
        }
        return $html;
    }

    protected function renderMediaModal(ilNewsItem $i): string
    {
        global $DIC;

        $media_path = $this->getMediaPath($i);
        $mime = ilObjMediaObject::getMimeType($media_path);

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $modal_html = "";

        if (in_array($mime, ["image/jpeg", "image/svg+xml", "image/gif", "image/png"])) {
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

    public function renderFooter(): string
    {
        $i = $this->getNewsItem();

        // like
        $this->ctrl->setParameterByClass("ilnewstimelinegui", "news_id", $i->getId());
        $this->like_gui->setObject(
            $i->getContextObjId(),
            $i->getContextObjType(),
            $i->getContextSubObjId(),
            (string) $i->getContextSubObjType(),
            $i->getId()
        );
        $html = $this->ctrl->getHTML($this->like_gui);

        // comments
        $notes_obj_type = ($i->getContextSubObjType() == "")
            ? $i->getContextObjType()
            : $i->getContextSubObjType();
        $comments_gui = $this->notes->gui()->getCommentsGUI(
            $i->getContextObjId(),
            $i->getContextSubObjId(),
            $notes_obj_type,
            $i->getId()
        );
        $comments_gui->setDefaultCommand("getWidget");
        $comments_gui->setShowEmptyListMessage(false);
        $comments_gui->setShowHeader(false);
        $html .= $comments_gui->getWidget();
        //$html .= $this->ctrl->getHTML($comments_gui);

        $this->ctrl->setParameterByClass("ilnewstimelinegui", "news_id", $this->std_request->getNewsId());

        return $html . $this->renderMediaModal($i);
    }

    protected function getMediaPath(ilNewsItem $i): string
    {
        $media_path = "";
        if ($i->getMobId() > 0) {
            $mob = new ilObjMediaObject($i->getMobId());
            $med = $mob->getMediaItem("Standard");
            if (strcasecmp("Reference", $med->getLocationType()) === 0) {
                $media_path = $med->getLocation();
            } else {
                $media_path = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
            }
        }
        return $media_path;
    }
}
