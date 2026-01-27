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

use ILIAS\News\Access\NewsAccess;
use ILIAS\News\Data\NewsCollection;
use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\Data\NewsItem;
use ILIAS\News\InternalDomainService;
use ILIAS\News\InternalGUIService;
use ILIAS\News\StandardGUIRequest;

/**
 * BlockGUI class for block NewsForContext
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_IsCalledBy ilNewsForContextBlockGUI: ilColumnGUI
 * @ilCtrl_Calls ilNewsForContextBlockGUI: ilNewsItemGUI
 */
class ilNewsForContextBlockGUI extends ilBlockGUI
{
    /**
     * object type names with settings->news settings subtab
     */
    public const OBJECTS_WITH_NEWS_SUBTAB = ["category", "course", "group", "forum"];
    public static string $block_type = "news";
    protected NewsAccess $news_access;
    protected bool $dynamic = false;
    protected bool $show_view_selection;

    /**
     * @var false|mixed|string|null
     */
    protected string $view;
    protected ilPropertyFormGUI $settings_form;
    protected ilHelpGUI $help;
    protected ilSetting $settings;
    protected ilTabsGUI $tabs;
    protected ilLogger $logger;

    protected StandardGUIRequest $std_request;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;

    protected bool $prevent_initial_loading = false;
    protected NewsCollection $collection;
    protected ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->logger = $DIC->logger()->news();
        $this->help = $DIC["ilHelp"];
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->logger = $DIC->logger()->news();

        $locator = $DIC->news()->internal();
        $this->std_request = $locator->gui()->standardRequest();
        $this->domain = $locator->domain();
        $this->gui = $locator->gui();
        $this->news_access = new NewsAccess($this->std_request->getRefId());

        $this->lng->loadLanguageModule("news");
        $DIC->help()->addHelpSection("news_block");

        $this->setBlockId((string) $this->ctrl->getContextObjId());
        $this->setLimit(5);
        $this->setEnableNumInfo(true);

        if (!$this->prevent_initial_loading) {
            $this->loadNewsData();
        }

        $this->setTitle($this->lng->txt("news_internal_news"));
        $this->setRowTemplate("tpl.block_row_news_for_context.html", "components/ILIAS/News");
        $this->allow_moving = false;
        $this->handleView();

        $this->setPresentation(self::PRES_SEC_LIST);
    }

    private function loadNewsData(): void
    {
        if ($this->std_request->getRefId() === 0) {
            $this->initData(new NewsCollection());
            return;
        }

        $collection = $this->domain->collection()->getNewsForContext(
            new NewsContext($this->std_request->getRefId(), $this->ctrl->getContextObjId(), $this->ctrl->getContextObjType()),
            new NewsCriteria(read_user_id: $this->user->getId()),
            $this->user->getId(),
            true
        );

        if ($this->ctrl->getContextObjType() !== 'frm') {
            $collection = $collection->groupForums(true);
        }
        $this->initData($collection->groupFiles());
    }

    protected function initData(NewsCollection $collection): void
    {
        $this->collection = $collection;
        $this->data = $collection->pluck('id', true);
    }

    /**
     * Method will be called before rendering the block. It lazily loads the required news items.
     */
    protected function preloadData(array $data): void
    {
        parent::preloadData($data);
        $this->collection->load(array_column($data, 0));
    }

    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        try {
            $info = $this->getNewsForId($data[0]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $this->ui->factory()->item()->standard($this->lng->txt('news_not_available'))
                ->withDescription($this->lng->txt('news_sorry_not_accessible_anymore'));
        }


        $props = [
            $this->lng->txt('date') => $info['creation_date'] ?? ''
        ];

        $item = $this->ui->factory()->item()->standard(
            $this->ui->factory()->link()->standard($info['news_title'] ?? '', $info['url'] ?? '')
        )->withProperties($props);

        if ($info['ref_id'] > 0) {
            $item = $item->withDescription($info['type_txt'] . ': ' . $info['obj_title']);
        }
        return $item;
    }

    private function getNewsForId(int $news_id): array
    {
        $item = $this->collection->getById($news_id);
        if ($item === null) {
            throw new \InvalidArgumentException("News item with id {$news_id} not found.");
        }

        $grouping = $this->collection->getGroupingFor($item);

        $creation_date = new ilDateTime($item->getCreationDate()->format('c'), IL_CAL_DATETIME);
        $title = ilStr::shortenWords(
            ilNewsItem::determineNewsTitle(
                $item->getContextObjType(),
                $item->getTitle(),
                $item->isContentIsLangVar(),
                $grouping ? $grouping['agg_ref_id'] : 0,
                $grouping ? $grouping['aggregation'] : [],
            )
        );

        $info = [
            'ref_id' => $item->getContextRefId(),
            'creation_date' => ilDatePresentation::formatDate($creation_date),
            'news_title' => $title,
        ];

        // title image type
        if ($item->getContextRefId() > 0) {
            $obj_id = $item->getContextObjId();
            $type = $item->getContextObjType();

            $lang_type = in_array($type, ['sahs', 'lm', 'htlm']) ? 'lres' : 'obj_' . $type;

            $type_txt = ($this->obj_def->isPlugin($item->getContextObjType()))
                ? ilObjectPlugin::lookupTxtById($item->getContextObjType(), $lang_type)
                : $this->lng->txt($lang_type);

            $info['type_txt'] = $type_txt;
            $info['type_icon'] = ilObject::_getIcon($obj_id, 'tiny', $type);
            $info['obj_title'] = ilStr::shortenWords(ilObject::_lookupTitle($obj_id));
            $info['user_read'] = $this->collection->isReadByUser($this->user->getId(), $news_id);

            $this->ctrl->setParameter($this, 'news_context', $item->getContextRefId());
        } else {
            $this->ctrl->setParameter($this, 'news_context', '');
        }

        $this->ctrl->setParameter($this, 'news_id', $item->getId());
        $info['url'] = $this->ctrl->getLinkTarget($this, 'showNews');
        $this->ctrl->clearParameters($this);

        return $info;
    }


    public function getBlockType(): string
    {
        return self::$block_type;
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    public static function getScreenMode(): string
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();

        if (strcasecmp($ilCtrl->getCmdClass(), ilNewsItemGUI::class) === 0) {
            return IL_SCREEN_FULL;
        }

        switch ($ilCtrl->getCmd()) {
            case "showNews":
            case "showFeedUrl":
                return IL_SCREEN_CENTER;

            case "editSettings":
            case "saveSettings":
                return IL_SCREEN_FULL;

            default:
                return IL_SCREEN_SIDE;
        }
    }

    public function executeCommand()
    {
        if (strcasecmp($this->ctrl->getNextClass(), ilNewsItemGUI::class) === 0) {
            $news_item_gui = new ilNewsItemGUI();
            $news_item_gui->setEnableEdit($this->getEnableEdit());
            return $this->ctrl->forwardCommand($news_item_gui);
        }

        $cmd = $this->ctrl->getCmd("getHTML");
        return $this->$cmd();
    }

    public function getHTML(): string
    {
        global $DIC;

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        $hide_block = ilBlockSetting::_lookup(
            $this->getBlockType(),
            "hide_news_block",
            0,
            (int) $this->block_id
        );

        if ($this->getProperty("title") != "") {
            $this->setTitle($this->getProperty("title"));
        }

        $public_feed = ilBlockSetting::_lookup(
            $this->getBlockType(),
            "public_feed",
            0,
            (int) $this->block_id
        );
        if ($public_feed && $enable_internal_rss) {
            // @todo: rss icon HTML: ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_RSS)
            $this->addBlockCommand(
                ILIAS_HTTP_PATH . "/feed.php?client_id=" . rawurlencode(CLIENT_ID) . "&" .
                    "ref_id=" . $this->std_request->getRefId(),
                $lng->txt("news_feed_url")
            );
        }

        // add edit commands
        if ($this->news_access->canAdd()) {
            $this->addBlockCommand(
                $ilCtrl->getLinkTargetByClass(ilNewsItemGUI::class, "editNews"),
                $lng->txt("edit")
            );

            $ilCtrl->setParameter($this, "add_mode", "block");
            $this->addBlockCommand(
                $ilCtrl->getLinkTargetByClass(ilNewsItemGUI::class, "createNewsItem"),
                $lng->txt("add")
            );
            $ilCtrl->setParameter($this, "add_mode", "");
        }

        if ($this->getProperty("settings")) {
            $ref_id = $this->std_request->getRefId();
            $obj_def = $DIC["objDefinition"];
            $obj_id = ilObject::_lookupObjectId($ref_id);
            $obj_type = ilObject::_lookupType($ref_id, true);
            $obj_class = strtolower($obj_def->getClassName($obj_type));
            $parent_gui = "ilobj" . $obj_class . "gui";

            $ilCtrl->setParameterByClass(ilContainerNewsSettingsGUI::class, "ref_id", $ref_id);

            if (in_array($obj_class, self::OBJECTS_WITH_NEWS_SUBTAB)) {
                $this->addBlockCommand(
                    $ilCtrl->getLinkTargetByClass([ilRepositoryGUI::class, $parent_gui, ilContainerNewsSettingsGUI::class], "show"),
                    $lng->txt("settings")
                );
            } else {
                // not sure if this code is still used anywhere, see discussion at
                // https://mantis.ilias.de/view.php?id=31801
                // If ILIAS 8 beta phase does not throw this exception, we can remove this part.
                //throw new ilException("News settings are deprecated.");
                // the info screen will call this
                $this->addBlockCommand(
                    $ilCtrl->getLinkTarget($this, "editSettings"),
                    $lng->txt("settings")
                );
            }
        }

        // do not display hidden repository news blocks for users
        // who do not have write permission
        if (!$this->getEnableEdit() && $this->getRepositoryMode() &&
            ilBlockSetting::_lookup(
                $this->getBlockType(),
                "hide_news_block",
                0,
                (int) $this->block_id
            )) {
            return "";
        }

        // do not display empty news blocks for users
        // who do not have write permission
        if (!$this->dynamic && !$this->getEnableEdit() && $this->getRepositoryMode() && count($this->getData()) === 0 &&
            (
                !$news_set->get("enable_rss_for_internal") ||
                !ilBlockSetting::_lookup(
                    $this->getBlockType(),
                    "public_feed",
                    0,
                    (int) $this->block_id
                )
            )) {
            return "";
        }

        $en = "";

        return parent::getHTML() . $en;
    }

    /**
     * Handles show/hide notification view and removes notifications if hidden.
     */
    public function handleView(): void
    {
        // it seems like this method does not change any state, so it may be removed in the future

        /*$ilUser = $this->user;

        $this->view = (string) ilBlockSetting::_lookup(
            $this->getBlockType(),
            "view",
            $ilUser->getId(),
            (int) $this->block_id
        );

        // check whether notices and messages exist
        $got_notices = $got_messages = false;
        foreach ($this->data as $row) {
            if ((int) ($row["priority"] ?? 0) === 0) {
                $got_notices = true;
            }
            if ((int) ($row["priority"] ?? 0) === 1) {
                $got_messages = true;
            }
        }
        $this->show_view_selection = false;

        if ($got_notices && $got_messages) {
            $this->show_view_selection = true;
        } elseif ($got_notices) {
            $this->view = "";
        }*/
    }

    public function getOverview(): string
    {
        $lng = $this->lng;

        return '<div class="small">' . (count($this->getData())) . " " . $lng->txt("news_news_items") . "</div>";
    }

    public function showNews(): string
    {
        $ui_renderer = $this->ui->renderer();
        $ui_factory = $this->ui->factory();

        $tpl = new ilTemplate("tpl.show_news.html", true, true, "components/ILIAS/News");
        $setting = new ilSetting("news");
        $enable_internal_rss = $setting->get("enable_rss_for_internal");

        if ($this->std_request->getNewsId() > 0) {
            $current_item = $this->collection->getById($this->std_request->getNewsId());
            $news_context = (int) $this->std_request->getNewsContext();
        } else {
            $current_item = $this->collection->pick($this->std_request->getNewsPage());
            $news_context = $current_item->getContextRefId();
        }

        if ($current_item === null) {
            return '';
        }

        if ($grouping = $this->collection->getGroupingFor($current_item)) {
            $news_list = $grouping['aggregation'];
        } else {
            $news_list = [$current_item];
        }

        for ($i = 0; $i < count($news_list); $i++) {
            /** @var NewsItem $item */
            $item = $news_list[$i];
            $item = $item->withContextRefId($news_context);

            ilNewsItem::_setRead($this->user->getId(), $this->std_request->getNewsId());

            $is_grouped_item = $i > 0;
            $legacy_news = $item->toLegacy();

            // author
            if (\ilObjUser::userExists([$item->getUserId()])) {
                $user = new ilObjUser($item->getUserId());
                $display_name = $user->getLogin();
            } else {
                // this should actually not happen, since news entries
                // should be deleted when the user is going to be removed
                $display_name = "&lt;" . strtolower($this->lng->txt("deleted")) . "&gt;";
            }
            $tpl->setCurrentBlock("user_info");
            $tpl->setVariable("VAL_AUTHOR", $display_name);
            $tpl->setVariable("TXT_AUTHOR", $this->lng->txt("author"));
            $tpl->parseCurrentBlock();

            // media player
            if ($item->getMobId() > 0 && ilObject::_exists($item->getMobId())) {
                $media_path = $this->getMediaPath($item->getMobId());
                $mime = ilObjMediaObject::getMimeType($media_path);
                if (in_array($mime, ["image/jpeg", "image/svg+xml", "image/gif", "image/png"])) {
                    $title = basename($media_path);
                    $html = $ui_renderer->render($ui_factory->image()->responsive($media_path, $title));
                } elseif (in_array($mime, ["video/mp4", "video/youtube", "video/vimeo"])) {
                    $video = $ui_factory->player()->video($media_path);
                    $html = $ui_renderer->render($video);
                } elseif (in_array($mime, ["audio/mpeg"])) {
                    $audio = $ui_factory->player()->audio($media_path);
                    $html = $ui_renderer->render($audio);
                } elseif (in_array($mime, ["application/pdf"])) {
                    $this->ctrl->setParameter($this, "news_id", $item->getId());
                    $link = $ui_factory->link()->standard(
                        basename($media_path),
                        $this->ctrl->getLinkTarget($this, "downloadMob")
                    );
                    $html = $ui_renderer->render($link);
                    $this->ctrl->setParameter($this, "news_id", null);
                } else {
                    // download?
                    $html = $mime;
                }

                $tpl->setCurrentBlock("player");
                $tpl->setVariable("PLAYER", $html);
                $tpl->parseCurrentBlock();
            }

            // access
            if ($enable_internal_rss && $item->getVisibility() !== '') {
                $tpl->setCurrentBlock("access");
                $tpl->setVariable("TXT_ACCESS", $this->lng->txt("news_news_item_visibility"));
                if ($item->getVisibility() === NEWS_PUBLIC ||
                    ($item->getPriority() === 0 &&
                        ilBlockSetting::_lookup(
                            "news",
                            "public_notifications",
                            0,
                            $item->getContextObjId()
                        ))) {
                    $tpl->setVariable("VAL_ACCESS", $this->lng->txt("news_visibility_public"));
                } else {
                    $tpl->setVariable("VAL_ACCESS", $this->lng->txt("news_visibility_users"));
                }
                $tpl->parseCurrentBlock();
            }

            // content
            $renderer = ilNewsRendererFactory::getRenderer($item->getContextObjType());
            if (trim($item->getContent()) !== '') {
                $renderer->setNewsItem($legacy_news, $item->getContextRefId());
                $tpl->setCurrentBlock("content");
                $tpl->setVariable("VAL_CONTENT", $renderer->getDetailContent());
                $tpl->parseCurrentBlock();
            }

            // update date
            if ($item->getUpdateDate() !== $item->getCreationDate()) {
                $tpl->setCurrentBlock("ni_update");
                $tpl->setVariable("TXT_LAST_UPDATE", $this->lng->txt("last_update"));
                $tpl->setVariable(
                    "VAL_LAST_UPDATE",
                    ilDatePresentation::formatDate(new ilDateTime($legacy_news->getUpdateDate(), IL_CAL_DATETIME))
                );
                $tpl->parseCurrentBlock();
            }

            // creation date
            if ($item->getCreationDate()->getTimestamp() !== 0) {
                $tpl->setCurrentBlock("ni_update");
                $tpl->setVariable(
                    "VAL_CREATION_DATE",
                    ilDatePresentation::formatDate(new ilDateTime($legacy_news->getCreationDate(), IL_CAL_DATETIME))
                );
                $tpl->setVariable("TXT_CREATED", $this->lng->txt("created"));
                $tpl->parseCurrentBlock();
            }

            // context / title
            if ($news_context > 0) {
                $obj_title = ilObject::_lookupTitle($item->getContextObjId());

                // file hack, not nice
                if ($item->getContextObjType() === "file") {
                    $this->ctrl->setParameterByClass(ilRepositoryGUI::class, "ref_id", $item->getContextRefId());
                    $url = $this->ctrl->getLinkTargetByClass(ilRepositoryGUI::class, "sendfile");
                    $this->ctrl->setParameterByClass(ilRepositoryGUI::class, "ref_id", $this->std_request->getRefId());

                    $button = $this->gui->button(
                        $this->lng->txt("download"),
                        $url
                    );

                    $tpl->setCurrentBlock("download");
                    $tpl->setVariable("BUTTON_DOWNLOAD", $button->render());
                    $tpl->parseCurrentBlock();
                }

                // forum hack, not nice
                $add = "";
                if ($item->getContextObjType() === "frm" &&
                    $item->getContextSubObjType() === "pos" &&
                    $item->getContextSubObjId() > 0
                ) {
                    $pos = $item->getContextSubObjId();
                    $thread = ilObjForumAccess::_getThreadForPosting($pos);
                    if ($thread > 0) {
                        $add = "_" . $thread . "_" . $pos;
                    }
                }

                // wiki hack, not nice
                if ($item->getContextObjType() === "wiki" &&
                    $item->getContextSubObjType() === "wpg" &&
                    $item->getContextSubObjId() > 0
                ) {
                    $wptitle = ilWikiPage::lookupTitle($item->getContextSubObjId());
                    if ($wptitle != "") {
                        $add = "_" . ilWikiUtil::makeUrlTitle($wptitle);
                    }
                }

                $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
                    $item->getContextObjType() . "_" . $item->getContextRefId() . $add;

                // lm page hack, not nice
                if ($item->getContextObjType() === "lm" &&
                    $item->getContextSubObjType() === "pg" &&
                    $item->getContextSubObjId() > 0
                ) {
                    $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
                        "pg_" . $item->getContextSubObjId() . "_" . $item->getContextRefId();
                }

                // blog posting hack, not nice
                if ($item->getContextObjType() === "blog" &&
                    $item->getContextSubObjType() === "blp" &&
                    $item->getContextSubObjId() > 0
                ) {
                    $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
                        "blog_" . $item->getContextRefId() . "_" . $item->getContextSubObjId();
                }

                $context_opened = false;
                $loc_context = $is_grouped_item ? $current_item->getContextRefId() : $news_context;
                $loc_stop = $is_grouped_item ? $news_context : null;
                if ($loc_context !== 0 && $loc_context !== $loc_stop) {
                    $tpl->setCurrentBlock("context");
                    $context_opened = true;
                    $cont_loc = new ilLocatorGUI();
                    $cont_loc->addContextItems($loc_context, true, (int) $loc_stop);
                    $tpl->setVariable("CONTEXT_LOCATOR", $cont_loc->getHTML());
                }

                if (!($grouping['no_context_title'] ?? false)) {
                    if (!$context_opened) {
                        $tpl->setCurrentBlock("context");
                    }
                    $tpl->setVariable("HREF_CONTEXT_TITLE", $url_target);
                    $tpl->setVariable("CONTEXT_TITLE", $obj_title);
                    $tpl->setVariable(
                        "IMG_CONTEXT_TITLE",
                        ilObject::_getIcon($item->getContextObjId(), "big", $item->getContextObjType())
                    );
                }
                if ($context_opened) {
                    $tpl->parseCurrentBlock();
                }

                $tpl->setVariable("HREF_TITLE", $url_target);
            }

            // title
            $tpl->setVariable(
                "VAL_TITLE",
                ilNewsItem::determineNewsTitle(
                    $item->getContextObjType(),
                    $item->getTitle(),
                    $item->isContentIsLangVar(),
                    (!$is_grouped_item && $grouping) ? $grouping['agg_ref_id'] : 0,
                    (!$is_grouped_item && $grouping) ? $grouping['aggregation'] : [],
                )
            );

            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM_ROW_CSS", $i % 2 === 0 ? "tblrow1" : "tblrow2");
            $tpl->parseCurrentBlock();
        }

        $content = $tpl->get();
        $title = $this->getProperty('title') ?? $this->lng->txt("news_internal_news");
        $panel = $ui_factory->panel()->standard($title, $ui_factory->legacy()->content($content));

        $pagination = $ui_factory->viewControl()->pagination()
                              ->withTargetURL($this->ctrl->getLinkTarget($this, "showNews"), "news_page")
                              ->withTotalEntries(count($this->getData()))
                              ->withPageSize(1)
                              ->withMaxPaginationButtons(10)
                              ->withCurrentPage($this->collection->getPageFor($current_item->getId()));
        $panel = $panel->withViewControls([$pagination]);

        return $ui_renderer->render($panel);
    }

    protected function getMediaPath(int $mob_id): string
    {
        $media_path = "";
        if ($mob_id > 0) {
            $mob = new ilObjMediaObject($mob_id);
            $media_path = $mob->getStandardSrc();
        }
        return $media_path;
    }

    public function makeClickable(string $a_str): string
    {
        // this fixes bug 8744. We assume that strings that contain < and >
        // already contain html, we do not handle these
        if (is_int(strpos($a_str, ">")) && is_int(strpos($a_str, "<"))) {
            return $a_str;
        }

        return ilUtil::makeClickable($a_str);
    }

    public function showNotifications(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        ilBlockSetting::_write(
            $this->getBlockType(),
            "view",
            "",
            $ilUser->getId(),
            (int) $this->block_id
        );

        // reload data
        $this->loadNewsData();
        $this->handleView();

        if ($ilCtrl->isAsynch()) {
            $this->send($this->getHTML());
        }

        $ilCtrl->returnToParent($this);
    }

    public function hideNotifications(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        ilBlockSetting::_write(
            $this->getBlockType(),
            "view",
            "hide_notifications",
            $ilUser->getId(),
            (int) $this->block_id
        );

        // reload data
        $this->loadNewsData();
        $this->handleView();

        if ($ilCtrl->isAsynch()) {
            $this->send($this->getHTML());
        }

        $ilCtrl->returnToParent($this);
    }

    /**
     * Show settings screen.
     */
    public function editSettings(): string
    {
        $this->initSettingsForm();
        return $this->settings_form->getHTML();
    }

    /**
     * Init setting form
     */
    public function initSettingsForm(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();

        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        $public = ilBlockSetting::_lookup(
            $this->getBlockType(),
            "public_notifications",
            0,
            (int) $this->block_id
        );
        $public_feed = ilBlockSetting::_lookup(
            $this->getBlockType(),
            "public_feed",
            0,
            (int) $this->block_id
        );
        $hide_block = ilBlockSetting::_lookup(
            $this->getBlockType(),
            "hide_news_block",
            0,
            (int) $this->block_id
        );
        $hide_news_per_date = ilBlockSetting::_lookup(
            $this->getBlockType(),
            "hide_news_per_date",
            0,
            (int) $this->block_id
        );
        $hide_news_date = ilBlockSetting::_lookup(
            $this->getBlockType(),
            "hide_news_date",
            0,
            (int) $this->block_id
        );

        if (is_string($hide_news_date) && $hide_news_date !== '') {
            $hide_news_date = explode(" ", $hide_news_date);
        }

        $this->settings_form = new ilPropertyFormGUI();
        $this->settings_form->setTitle($lng->txt("news_settings"));

        // hide news block for learners
        if ($this->getProperty("hide_news_block_option")) {
            $ch = new ilCheckboxInputGUI(
                $lng->txt("news_hide_news_block"),
                "hide_news_block"
            );
            $ch->setInfo($lng->txt("news_hide_news_block_info"));
            $ch->setChecked((bool) $hide_block);
            $this->settings_form->addItem($ch);

            $hnpd = new ilCheckboxInputGUI(
                $lng->txt("news_hide_news_per_date"),
                "hide_news_per_date"
            );
            $hnpd->setInfo($lng->txt("news_hide_news_per_date_info"));
            $hnpd->setChecked((bool) $hide_news_per_date);

            $dt_prop = new ilDateTimeInputGUI(
                $lng->txt("news_hide_news_date"),
                "hide_news_date"
            );
            $dt_prop->setRequired(true);
            if (is_array($hide_news_date) && count($hide_news_date) === 2) {
                $dt_prop->setDate(new ilDateTime($hide_news_date[0] . ' ' . $hide_news_date[1], IL_CAL_DATETIME));
            }
            $dt_prop->setShowTime(true);
            $hnpd->addSubItem($dt_prop);

            $this->settings_form->addItem($hnpd);
        }

        // default visibility
        if ($enable_internal_rss && $this->getProperty("default_visibility_option")) {
            $default_visibility = ilBlockSetting::_lookup(
                $this->getBlockType(),
                "default_visibility",
                0,
                (int) $this->block_id
            );
            if ($default_visibility == "") {
                $default_visibility =
                    ilNewsItem::_getDefaultVisibilityForRefId($this->std_request->getRefId());
            }

            // Default Visibility
            $radio_group = new ilRadioGroupInputGUI($lng->txt("news_default_visibility"), "default_visibility");
            $radio_option = new ilRadioOption($lng->txt("news_visibility_users"), "users");
            $radio_group->addOption($radio_option);
            $radio_option = new ilRadioOption($lng->txt("news_visibility_public"), "public");
            $radio_group->addOption($radio_option);
            $radio_group->setInfo($lng->txt("news_news_item_visibility_info"));
            $radio_group->setRequired(false);
            $radio_group->setValue($default_visibility);
            $this->settings_form->addItem($radio_group);
        }

        // public notifications
        if ($enable_internal_rss && $this->getProperty("public_notifications_option")) {
            $ch = new ilCheckboxInputGUI(
                $lng->txt("news_notifications_public"),
                "notifications_public"
            );
            $ch->setInfo($lng->txt("news_notifications_public_info"));
            $ch->setChecked((bool) $public);
            $this->settings_form->addItem($ch);
        }

        // extra rss feed
        if ($enable_internal_rss) {
            $ch = new ilCheckboxInputGUI(
                $lng->txt("news_public_feed"),
                "notifications_public_feed"
            );
            $ch->setInfo($lng->txt("news_public_feed_info"));
            $ch->setChecked((bool) $public_feed);
            $this->settings_form->addItem($ch);
        }

        $this->settings_form->addCommandButton("saveSettings", $lng->txt("save"));
        $this->settings_form->addCommandButton("cancelSettings", $lng->txt("cancel"));
        $this->settings_form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Add inputs to the container news settings form to configure also the contextBlock options.
     */
    public static function addToSettingsForm(ilFormPropertyGUI $a_input): void
    {
        global $DIC;

        $std_request = $DIC->news()
            ->internal()
            ->gui()
            ->standardRequest();

        $lng = $DIC->language();
        $block_id = $DIC->ctrl()->getContextObjId();

        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        $public_feed = ilBlockSetting::_lookup(
            self::$block_type,
            "public_feed",
            0,
            $block_id
        );
        $default_visibility = ilBlockSetting::_lookup(self::$block_type, "default_visibility", 0, $block_id);
        if ($default_visibility == "") {
            $default_visibility =
                ilNewsItem::_getDefaultVisibilityForRefId($std_request->getRefId());
        }
        $radio_group = new ilRadioGroupInputGUI($lng->txt("news_default_visibility"), "default_visibility");
        $radio_option = new ilRadioOption($lng->txt("news_visibility_users"), "users");
        $radio_group->addOption($radio_option);
        $radio_option = new ilRadioOption($lng->txt("news_visibility_public"), "public");
        $radio_group->addOption($radio_option);
        $radio_group->setInfo($lng->txt("news_news_item_visibility_info"));
        $radio_group->setRequired(false);
        $radio_group->setValue($default_visibility);
        $a_input->addSubItem($radio_group);

        // extra rss feed
        if ($enable_internal_rss) {
            $radio_rss = new ilCheckboxInputGUI(
                $lng->txt("news_public_feed"),
                "notifications_public_feed"
            );
            $radio_rss->setInfo($lng->txt("news_public_feed_info"));
            $radio_rss->setChecked((bool) $public_feed);
            $a_input->addSubItem($radio_rss);
        }
    }

    public static function writeSettings(array $a_values): void
    {
        global $DIC;

        $block_id = $DIC->ctrl()->getContextObjId();
        foreach ($a_values as $key => $value) {
            ilBlockSetting::_write(self::$block_type, (string) $key, (string) $value, 0, $block_id);
        }
    }

    public function cancelSettings(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->returnToParent($this);
    }

    public function saveSettings(): string
    {
        $ilCtrl = $this->ctrl;

        $this->initSettingsForm();
        $form = $this->settings_form;
        if ($form->checkInput()) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                ilBlockSetting::_write(
                    $this->getBlockType(),
                    "public_notifications",
                    $form->getInput("notifications_public"),
                    0,
                    (int) $this->block_id
                );
                ilBlockSetting::_write(
                    $this->getBlockType(),
                    "public_feed",
                    $form->getInput("notifications_public_feed"),
                    0,
                    (int) $this->block_id
                );
                ilBlockSetting::_write(
                    $this->getBlockType(),
                    "default_visibility",
                    $form->getInput("default_visibility"),
                    0,
                    (int) $this->block_id
                );
            }

            if ($this->getProperty("hide_news_block_option")) {
                ilBlockSetting::_write(
                    $this->getBlockType(),
                    "hide_news_block",
                    $form->getInput("hide_news_block"),
                    0,
                    (int) $this->block_id
                );
                ilBlockSetting::_write(
                    $this->getBlockType(),
                    "hide_news_per_date",
                    $form->getInput("hide_news_per_date"),
                    0,
                    (int) $this->block_id
                );

                // hide date
                $hd = $this->settings_form->getItemByPostVar("hide_news_date");
                $hide_date = $hd->getDate();
                if ($hide_date instanceof ilDateTime && $form->getInput("hide_news_per_date")) {
                    ilBlockSetting::_write(
                        $this->getBlockType(),
                        "hide_news_date",
                        $hide_date->get(IL_CAL_DATETIME),
                        0,
                        (int) $this->block_id
                    );
                } else {
                    ilBlockSetting::_write(
                        $this->getBlockType(),
                        "hide_news_date",
                        "",
                        0,
                        (int) $this->block_id
                    );
                }
            }

            $this->domain->collection()->invalidateCache($this->user->getId());

            $ilCtrl->returnToParent($this);
        } else {
            $this->settings_form->setValuesByPost();
            return $this->settings_form->getHTML();
        }
        return "";
    }

    public function showFeedUrl(): string
    {
        $lng = $this->lng;
        $ilUser = $this->user;

        $title = ilObject::_lookupTitle((int) $this->block_id);

        $tpl = new ilTemplate("tpl.show_feed_url.html", true, true, "components/ILIAS/News");
        $tpl->setVariable(
            "TXT_TITLE",
            sprintf($lng->txt("news_feed_url_for"), $title)
        );
        $tpl->setVariable("TXT_INFO", $lng->txt("news_get_feed_info"));
        $tpl->setVariable("TXT_FEED_URL", $lng->txt("news_feed_url"));
        $tpl->setVariable(
            "VAL_FEED_URL",
            ILIAS_HTTP_PATH . "/feed.php?client_id=" . rawurlencode(CLIENT_ID) . "&user_id=" . $ilUser->getId() .
                "&obj_id=" . $this->block_id .
                "&hash=" . ilObjUser::_lookupFeedHash($ilUser->getId(), true)
        );
        $tpl->setVariable(
            "VAL_FEED_URL_TXT",
            ILIAS_HTTP_PATH . "/feed.php?client_id=" . rawurlencode(CLIENT_ID) . "&<br />user_id=" . $ilUser->getId() .
                "&obj_id=" . $this->block_id .
                "&hash=" . ilObjUser::_lookupFeedHash($ilUser->getId(), true)
        );

        $panel = $this->ui->factory()->panel()->standard(
            $lng->txt("news_internal_news"),
            $this->ui->factory()->legacy()->content($tpl->get())
        );

        return $this->ui->renderer()->render($panel);
    }

    public function getDynamicReload(): string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilCtrl->setParameterByClass(
            ilColumnGUI::class,
            "block_id",
            "block_" . $this->getBlockType() . "_" . $this->getBlockId()
        );

        $rel_tpl = new ilTemplate("tpl.dynamic_reload.html", true, true, "components/ILIAS/News");
        $rel_tpl->setVariable("TXT_LOADING", $lng->txt("news_loading_news"));
        $rel_tpl->setVariable("BLOCK_ID", "block_" . $this->getBlockType() . "_" . $this->getBlockId());
        $rel_tpl->setVariable(
            "TARGET",
            $ilCtrl->getLinkTargetByClass(ilColumnGUI::class, "updateBlock", "", true)
        );

        // no JS
        $rel_tpl->setVariable("TXT_NEWS_CLICK_HERE", $lng->txt("news_no_js_click_here"));
        $rel_tpl->setVariable(
            "TARGET_NO_JS",
            $ilCtrl->getLinkTarget($this, "disableJS")
        );

        return $rel_tpl->get();
    }

    public function getJSEnabler(): string
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass(
            ilColumnGUI::class,
            "block_id",
            "block_" . $this->getBlockType() . "_" . $this->getBlockId()
        );
        //echo "hh";
        $rel_tpl = new ilTemplate("tpl.js_enabler.html", true, true, "components/ILIAS/News");
        $rel_tpl->setVariable("BLOCK_ID", "block_" . $this->getBlockType() . "_" . $this->getBlockId());
        $rel_tpl->setVariable(
            "TARGET",
            $ilCtrl->getLinkTarget($this, "enableJS", "", true, false)
        );

        return $rel_tpl->get();
    }


    public function disableJS(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        ilSession::set("il_feed_js", "n");
        $ilUser->writePref("il_feed_js", "n");
        $ilCtrl->returnToParent($this);
    }

    public function enableJS(): void
    {
        $ilUser = $this->user;
        ilSession::set("il_feed_js", "y");
        $ilUser->writePref("il_feed_js", "y");
        $this->send($this->getHTML());
    }

    public function getNoItemFoundContent(): string
    {
        return $this->lng->txt("news_no_news_items");
    }

    protected function downloadMob(): void
    {
        $news_id = $this->std_request->getNewsId();
        $news = new ilNewsItem($news_id);
        $news->deliverMobFile("Standard", true);
    }
}
