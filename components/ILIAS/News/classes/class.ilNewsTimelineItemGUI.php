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

use ILIAS\News\Data\NewsItem;
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
    protected ilObjectDefinition $obj_def;
    protected ilObjUser $user;
    protected bool $user_edit_all;
    protected int $ref_id;
    protected ilCtrl $ctrl;
    protected StandardGUIRequest $std_request;
    /**
     * @var array<int, \ILIAS\UI\Component\Image\Image>
     */
    protected array $item_image = [];
    /**
     * @var array<int, \ILIAS\UI\Component\Modal\Modal>
     */
    protected array $item_modal = [];

    protected readonly ilObjMediaObject $media_object;

    protected function __construct(
        protected readonly NewsItem $news_item,
        protected readonly ilLikeGUI $like_gui
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->obj_def = $DIC["objDefinition"];

        $this->std_request = $DIC->news()
            ->internal()
            ->gui()
            ->standardRequest();
        $this->ref_id = $this->std_request->getRefId();
        $this->gui = $DIC->news()
            ->internal()
            ->gui();
        $this->notes = $DIC->notes();
        $this->media_object = new ilObjMediaObject($this->news_item->getMobId());
    }

    public static function getInstance(
        NewsItem $news_item,
        ilLikeGUI $like_gui
    ): self {
        return new self($news_item, $like_gui);
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
        return new ilDateTime($this->news_item->getCreationDate()->format('c'), IL_CAL_DATETIME);
    }

    public function render(): string
    {
        $tpl = new ilTemplate("tpl.timeline_item.html", true, true, "components/ILIAS/News");
        $ui_factory = $this->gui->ui()->factory();
        $ui_renderer = $this->gui->ui()->renderer();

        $news_renderer = ilNewsRendererFactory::getRenderer($this->news_item->getContextObjType());
        $news_renderer->setLanguage($this->lng->getLangKey());
        $news_renderer->setNewsItem($this->news_item->toLegacy(), $this->news_item->getContextRefId());

        $obj_id = $this->news_item->getContextObjId();

        // edited?
        if ($this->news_item->getCreationDate() !== $this->news_item->getUpdateDate()) {
            $tpl->setCurrentBlock("edited");
            $update_date = new ilDateTime($this->news_item->getUpdateDate()->format('c'), IL_CAL_DATETIME);
            $tpl->setVariable("TXT_EDITED", $this->lng->txt("cont_news_edited"));
            if (
                $this->news_item->getUpdateUserId() > 0 &&
                ($this->news_item->getUpdateUserId() !== $this->news_item->getUserId())
            ) {
                $tpl->setVariable("TXT_USR_EDITED", ilUserUtil::getNamePresentation(
                    $this->news_item->getUpdateUserId(),
                    false,
                    true,
                    $this->ctrl->getLinkTargetByClass(ilNewsTimelineGUI::class)
                ) . " - ");
            }
            $tpl->setVariable("TIME_EDITED", ilDatePresentation::formatDate($update_date));
            $tpl->parseCurrentBlock();
        }

        // context object link
        if ($this->news_item->getContextRefId() > 0 && $this->ref_id !== $this->news_item->getContextRefId()) {
            $tpl->setCurrentBlock("object");
            $tpl->setVariable("OBJ_TITLE", ilObject::_lookupTitle($obj_id));
            $tpl->setVariable("OBJ_IMG", ilObject::_getIcon($obj_id));
            $tpl->setVariable("OBJ_HREF", $news_renderer->getObjectLink());
            $tpl->parseCurrentBlock();
        }

        // media
        if ($this->news_item->getMobId() > 0 && ilObject::_exists($this->news_item->getMobId())) {
            $media = $this->renderMedia();
            $tpl->setCurrentBlock("player");
            $tpl->setVariable("PLAYER", $media);
            $tpl->parseCurrentBlock();
        }

        $p = $this->gui->profile();
        $tpl->setVariable("USER_AVATAR", $this->gui->ui()->renderer()->render(
            $p->getAvatar($this->news_item->getUserId())
        ));
        $tpl->setVariable(
            "TITLE",
            ilNewsItem::determineNewsTitle(
                $this->news_item->getContextObjType(),
                $this->news_item->getTitle(),
                $this->news_item->isContentIsLangVar()
            )
        );

        // content
        $tpl->setVariable("CONTENT", $news_renderer->getTimelineContent());

        $tpl->setVariable("TXT_USR", $p->getNamePresentation(
            $this->news_item->getUserId(),
            true,
            $this->ctrl->getLinkTargetByClass(ilNewsTimelineGUI::class)
        ));

        $tpl->setVariable("TIME", ilDatePresentation::formatDate($this->getDateTime()));

        // actions
        $actions = [];

        if (
            $this->news_item->getPriority() === 1 &&
            ($this->news_item->getUserId() === $this->user->getId() || $this->getUserEditAll())
        ) {
            if (!$news_renderer->preventEditing()) {
                $i = $this->news_item;
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

    protected function renderMedia(): string
    {
        $media_path = $this->getMediaPath();
        $mime = ilObjMediaObject::getMimeType($media_path);

        $ui_factory = $this->gui->ui()->factory();
        $ui_renderer = $this->gui->ui()->renderer();

        if (in_array($mime, ["image/jpeg", "image/svg+xml", "image/gif", "image/png"])) {
            if (isset($this->item_image[$this->news_item->getId()]) && isset($this->item_modal[$this->news_item->getId()])) {
                $image = $this->item_image[$this->news_item->getId()];
            } else {
                $title = $this->media_object->getTitle();
                $image = $ui_factory->image()->responsive($media_path, $title);
                $modal_page = $ui_factory->modal()->lightboxImagePage($image, $title);
                $modal = $ui_factory->modal()->lightbox($modal_page);
                $image = $image->withAction($modal->getShowSignal());
                $this->item_image[$this->news_item->getId()] = $image;
                $this->item_modal[$this->news_item->getId()] = $modal;
            }
            $html = $ui_renderer->render($image);
        } elseif (in_array($mime, ["video/mp4", "video/youtube", "video/vimeo"])) {
            $video = $ui_factory->player()->video($media_path);
            $html = $ui_renderer->render($video);
        } elseif (in_array($mime, ["audio/mpeg"])) {
            $audio = $ui_factory->player()->audio($media_path);
            $html = $ui_renderer->render($audio);
        } elseif (in_array($mime, ["application/pdf"])) {
            $this->ctrl->setParameterByClass(ilNewsTimelineGUI::class, "news_id", $this->news_item->getId());
            $link = $ui_factory->link()->standard(
                basename($media_path),
                $this->ctrl->getLinkTargetByClass(ilNewsTimelineGUI::class, "downloadMob")
            );
            $html = $ui_renderer->render($link);
            $this->ctrl->setParameterByClass(ilNewsTimelineGUI::class, "news_id", null);
        } else {
            $html = "";
        }
        return $html;
    }

    protected function renderMediaModal(): string
    {
        $ui_factory = $this->gui->ui()->factory();
        $ui_renderer = $this->gui->ui()->renderer();

        if (isset($this->item_image[$this->news_item->getId()]) && isset($this->item_modal[$this->news_item->getId()])) {
            $modal = $this->item_modal[$this->news_item->getId()];
            return $ui_renderer->render($modal);
        }

        $media_path = $this->getMediaPath();
        $mime = ilObjMediaObject::getMimeType($media_path);

        $modal_html = "";

        if (in_array($mime, ["image/jpeg", "image/svg+xml", "image/gif", "image/png"])) {
            $title = $this->media_object->getTitle();
            $image = $ui_factory->image()->responsive($media_path, $title);
            $modal_page = $ui_factory->modal()->lightboxImagePage($image, $title);
            $modal = $ui_factory->modal()->lightbox($modal_page);
            $image = $image->withAction($modal->getShowSignal());
            $this->item_image[$this->news_item->getId()] = $image;
            $this->item_modal[$this->news_item->getId()] = $modal;
            $modal_html = $ui_renderer->render($modal);
        }
        return $modal_html;
    }

    public function renderFooter(): string
    {
        // like
        $this->ctrl->setParameterByClass(ilNewsTimelineGUI::class, "news_id", $this->news_item->getId());
        $this->like_gui->setObject(
            $this->news_item->getContextObjId(),
            $this->news_item->getContextObjType(),
            $this->news_item->getContextSubObjId(),
            (string) $this->news_item->getContextSubObjType(),
            $this->news_item->getId()
        );
        $html = $this->ctrl->getHTML($this->like_gui);

        // comments
        $notes_obj_type = ($this->news_item->getContextSubObjType() === null)
            ? $this->news_item->getContextObjType()
            : $this->news_item->getContextSubObjType();
        $comments_gui = $this->notes->gui()->getCommentsGUI(
            $this->news_item->getContextObjId(),
            $this->news_item->getContextSubObjId(),
            $notes_obj_type,
            $this->news_item->getId()
        );
        $comments_gui->setDefaultCommand("getWidget");
        $comments_gui->setShowEmptyListMessage(false);
        $comments_gui->setShowHeader(false);
        $html .= $comments_gui->getWidget();
        //$html .= $this->ctrl->getHTML($comments_gui);

        $this->ctrl->setParameterByClass(ilNewsTimelineGUI::class, "news_id", $this->std_request->getNewsId());

        return $html . $this->renderMediaModal($this->news_item);
    }

    protected function getMediaPath(): string
    {
        return $this->news_item->getMobId() > 0 ? $this->media_object->getStandardSrc() : "";
    }
}
