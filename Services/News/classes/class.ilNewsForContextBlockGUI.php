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
    protected bool $cache_hit = false;
    protected bool $dynamic = false;
    protected ilNewsCache $acache;
    protected bool $show_view_selection;
    protected bool $new_rendering = true;

    /**
     * @var false|mixed|string|null
     */
    protected string $view;
    protected ilPropertyFormGUI $settings_form;
    protected ilHelpGUI $help;
    protected ilSetting $settings;
    protected ilTabsGUI $tabs;
    public static string $block_type = "news";
    public static array $st_data;
    protected ilObjectDefinition $obj_definition;
    protected StandardGUIRequest $std_request;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->help = $DIC["ilHelp"];
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->obj_definition = $DIC["objDefinition"];
        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        $ilHelp = $DIC["ilHelp"];

        parent::__construct();

        $lng->loadLanguageModule("news");
        $ilHelp->addHelpSection("news_block");

        $this->setBlockId((string) $ilCtrl->getContextObjId());
        $this->setLimit(5);
        $this->setEnableNumInfo(true);

        $this->dynamic = false;
        $this->acache = new ilNewsCache();
        $cres = unserialize(
            $this->acache->getEntry($ilUser->getId() . ":" . $this->std_request->getRefId()),
            ["allowed_classes" => false]
        );
        $this->cache_hit = false;

        if ($this->acache->getLastAccessStatus() === "hit" && is_array($cres)) {
            self::$st_data = ilNewsItem::prepareNewsDataFromCache($cres);
            $this->cache_hit = true;
        }
        if ($this->getDynamic() && !$this->cache_hit) {
            $this->dynamic = true;
            $data = [];
        } elseif (!empty(self::$st_data)) {
            $data = self::$st_data;
        } else {
            $data = $this->getNewsData();
            self::$st_data = $data;
        }

        $this->setTitle($lng->txt("news_internal_news"));
        $this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
        $this->setData($data);
        $this->allow_moving = false;
        $this->handleView();

        $this->setPresentation(self::PRES_SEC_LIST);
    }

    public function getNewsData(): array
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $this->acache = new ilNewsCache();
        /*		$cres = $this->acache->getEntry($ilUser->getId().":".$_GET["ref_id"]);
                if ($this->acache->getLastAccessStatus() == "hit" && false)
                {
                    $news_data = unserialize($cres);
                }
                else
                {*/
        $news_item = new ilNewsItem();
        $news_item->setContextObjId($ilCtrl->getContextObjId());
        $news_item->setContextObjType($ilCtrl->getContextObjType());

        // workaround, better: reduce constructor and introduce
        //$prevent_aggregation = $this->getProperty("prevent_aggregation");
        $prevent_aggregation = true;
        if ($ilCtrl->getContextObjType() !== "frm") {
            $forum_grouping = true;
        } else {
            $forum_grouping = false;
        }


        $news_data = $news_item->getNewsForRefId(
            $this->std_request->getRefId(),
            false,
            false,
            0,
            $prevent_aggregation,
            $forum_grouping
        );

        $this->acache->storeEntry(
            $ilUser->getId() . ":" . $this->std_request->getRefId(),
            serialize($news_data)
        );

        //		}
        //var_dump($news_data);
        return $news_data;
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

        if (strtolower($ilCtrl->getCmdClass()) === "ilnewsitemgui") {
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
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            case "ilnewsitemgui":
                $news_item_gui = new ilNewsItemGUI();
                $news_item_gui->setEnableEdit($this->getEnableEdit());
                $html = $ilCtrl->forwardCommand($news_item_gui);
                return $html;

            default:
                return $this->$cmd();
        }
    }

    public function fillDataSection(): void
    {
        if ($this->dynamic) {
            $this->setDataSection($this->getDynamicReload());
        } elseif (count($this->getData()) > 0) {
            parent::fillDataSection();
        } else {
            $this->setDataSection($this->getOverview());
        }
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
        if ($this->getEnableEdit()) {
            $this->addBlockCommand(
                $ilCtrl->getLinkTargetByClass("ilnewsitemgui", "editNews"),
                $lng->txt("edit")
            );

            $ilCtrl->setParameter($this, "add_mode", "block");
            $this->addBlockCommand(
                $ilCtrl->getLinkTargetByClass("ilnewsitemgui", "createNewsItem"),
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

            $ilCtrl->setParameterByClass("ilcontainernewssettingsgui", "ref_id", $ref_id);

            if (in_array($obj_class, self::OBJECTS_WITH_NEWS_SUBTAB)) {
                $this->addBlockCommand(
                    $ilCtrl->getLinkTargetByClass(["ilrepositorygui", $parent_gui, "ilcontainernewssettingsgui"], "show"),
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
        $ilUser = $this->user;

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
        }
    }

    /**
     * get flat list for dashboard
     */
    public function fillRow(array $a_set): void
    {
        $info = $this->getInfoForData($a_set);

        $this->tpl->setCurrentBlock("long");
        $this->tpl->setVariable("VAL_CREATION_DATE", $info["creation_date"]);
        $this->tpl->parseCurrentBlock();

        if ($info["ref_id"] > 0) {
            $this->tpl->setCurrentBlock("news_context");
            $this->tpl->setVariable("TYPE", $info["type_txt"]);
            $this->tpl->setVariable("IMG_TYPE", $info["type_icon"]);
            $this->tpl->setVariable("TITLE", $info["obj_title"]);
            if ($info["user_read"] > 0) {
                $this->tpl->setVariable("TITLE_CLASS", 'class="light"');
            }

            $this->tpl->parseCurrentBlock();
        }

        // title
        $this->tpl->setVariable("VAL_TITLE", $info["news_title"]);

        if ($info["user_read"] > 0) {
            $this->tpl->setVariable("A_CLASS", 'class="light"');
        }

        $this->tpl->setVariable("HREF_SHOW", $info["url"]);
    }

    public function getInfoForData(array $news): array
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $obj_definition = $this->obj_definition;

        $info = [];

        $info["ref_id"] = $news["ref_id"];
        $info["creation_date"] =
            ilDatePresentation::formatDate(new ilDateTime($news["creation_date"], IL_CAL_DATETIME));

        // title image type
        if ($news["ref_id"] > 0) {
            if (isset($news["agg_ref_id"]) && $news["agg_ref_id"] > 0) {
                $obj_id = ilObject::_lookupObjId($news["agg_ref_id"]);
                $type = ilObject::_lookupType($obj_id);
                $context_ref = $news["agg_ref_id"];
            } else {
                $obj_id = $news["context_obj_id"];
                $type = $news["context_obj_type"];
                $context_ref = $news["ref_id"];
            }

            $lang_type = in_array($type, ["sahs", "lm", "htlm"])
                ? "lres"
                : "obj_" . $type;

            $type_txt = ($obj_definition->isPlugin($news["context_obj_type"]))
                ? ilObjectPlugin::lookupTxtById($news["context_obj_type"], $lang_type)
                : $lng->txt($lang_type);


            $info["type_txt"] = $type_txt;
            $info["type_icon"] = ilObject::_getIcon($obj_id, "tiny", $type);
            $info["obj_title"] = ilStr::shortenWords(ilObject::_lookupTitle($obj_id));
            $info["user_read"] = $news["user_read"];

            $ilCtrl->setParameter($this, "news_context", $context_ref);
        } else {
            $ilCtrl->setParameter($this, "news_context", "");
        }

        // title
        $info["news_title"] =
            ilStr::shortenWords(
                ilNewsItem::determineNewsTitle(
                    $news["context_obj_type"],
                    $news["title"],
                    $news["content_is_lang_var"],
                    $news["agg_ref_id"] ?? 0,
                    $news["aggregation"] ?? []
                )
            );


        $ilCtrl->setParameter($this, "news_id", $news["id"]);
        $info["url"] =
            $ilCtrl->getLinkTarget($this, "showNews");
        $ilCtrl->clearParameters($this);

        return $info;
    }

    public function getOverview(): string
    {
        $lng = $this->lng;

        return '<div class="small">' . (count($this->getData())) . " " . $lng->txt("news_news_items") . "</div>";
    }

    public function showNews(): string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilAccess = $this->access;

        // workaround for dynamic mode (if cache is disabled, showNews has no data)
        if (empty(self::$st_data)) {
            $this->setData($this->getNewsData());
        }

        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");


        $tpl = new ilTemplate("tpl.show_news.html", true, true, "Services/News");

        // get current item in data set
        $previous = "";
        reset($this->data);
        $c = current($this->data);
        $curr_cnt = 1;

        if ($this->std_request->getNewsId() > 0) {
            $news = new ilNewsItem($this->std_request->getNewsId());
            $news_context = $this->std_request->getNewsContext();
            while ($c["id"] > 0 && (int) $c["id"] !== $this->std_request->getNewsId()) {
                $previous = $c;
                $c = next($this->data);
                $curr_cnt++;
            }
        } else {
            $news_page = $this->std_request->getNewsPage();
            $news_context = 0;
            while ($curr_cnt - 1 < $news_page) {
                $previous = $c;
                $c = next($this->data);
                if ($curr_cnt - 1 === $news_page) {
                }
                $curr_cnt++;
            }
            $news = new ilNewsItem($c["id"]);
            if ($c["ref_id"] > 0) {
                $news_context = (int) $c["ref_id"];
            }
        }


        if (!is_array($c) && is_object($news) && $news->getId() > 0
            && ilNewsItem::_lookupContextObjId($news->getId()) !== $ilCtrl->getContextObjId()) {
            throw new ilException("News ID does not match object context.");
        }


        // collect news items to show
        $news_list = [];
        if (isset($c["aggregation"]) && is_array($c["aggregation"])) {	// we have an aggregation
            $news_list[] = [
                "ref_id" => $c["agg_ref_id"],
                "agg_ref_id" => $c["agg_ref_id"],
                "aggregation" => $c["aggregation"],
                "user_id" => "",
                "content_type" => "text",
                "mob_id" => 0,
                "visibility" => "",
                "content" => "",
                "content_long" => "",
                "update_date" => $news->getUpdateDate(),
                "creation_date" => "",
                "content_is_lang_var" => false,
                "loc_context" => $news_context,
                "context_obj_type" => $news->getContextObjType(),
                "title" => ""
            ];

            foreach ($c["aggregation"] as $c_item) {
                ilNewsItem::_setRead($ilUser->getId(), $c_item["id"]);
                $c_item["loc_context"] = $c_item["ref_id"];
                $c_item["loc_stop"] = $news_context;
                $news_list[] = $c_item;
            }
        } else {								// no aggregation, simple news item
            $news_list[] = [
                "id" => $news->getId(),
                "ref_id" => $news_context,
                "user_id" => $news->getUserId(),
                "content_type" => $news->getContentType(),
                "mob_id" => $news->getMobId(),
                "visibility" => $news->getVisibility(),
                "priority" => $news->getPriority(),
                "content" => $news->getContent(),
                "content_long" => $news->getContentLong(),
                "update_date" => $news->getUpdateDate(),
                "creation_date" => $news->getCreationDate(),
                "context_sub_obj_type" => $news->getContextSubObjType(),
                "context_obj_type" => $news->getContextObjType(),
                "context_sub_obj_id" => $news->getContextSubObjId(),
                "content_is_lang_var" => $news->getContentIsLangVar(),
                "content_text_is_lang_var" => $news->getContentTextIsLangVar(),
                "loc_context" => $news_context,
                "title" => $news->getTitle()
            ];
            ilNewsItem::_setRead($ilUser->getId(), $this->std_request->getNewsId());
        }

        $row_css = "";
        $cache_deleted = false;
        foreach ($news_list as $item) {
            $row_css = ($row_css !== "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";

            if ($item["ref_id"] > 0 && !$ilAccess->checkAccess("read", "", $item["ref_id"])) {
                $tpl->setCurrentBlock("content");
                $tpl->setVariable("VAL_CONTENT", $lng->txt("news_sorry_not_accessible_anymore"));
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("ITEM_ROW_CSS", $row_css);
                $tpl->parseCurrentBlock();
                if (!$cache_deleted) {
                    $this->acache->deleteEntry($ilUser->getId() . ":" . $this->std_request->getRefId());
                    $cache_deleted = true;
                }
                continue;
            }

            // user
            if ($item["user_id"] > 0 && ilObject::_exists($item["user_id"])) {
                // get login
                if (ilObjUser::_exists($item["user_id"])) {
                    $user = new ilObjUser($item["user_id"]);
                    $displayname = $user->getLogin();
                } else {
                    // this should actually not happen, since news entries
                    // should be deleted when the user is going to be removed
                    $displayname = "&lt;" . strtolower($lng->txt("deleted")) . "&gt;";
                }

                $tpl->setCurrentBlock("user_info");
                $tpl->setVariable("VAL_AUTHOR", $displayname);
                $tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
                $tpl->parseCurrentBlock();
            }

            // media player
            $ui_renderer = $this->ui->renderer();
            $ui_factory = $this->ui->factory();

            if ($item["mob_id"] > 0 && ilObject::_exists((int) $item["mob_id"])) {
                $media_path = $this->getMediaPath((int) $item["mob_id"]);
                $mime = ilObjMediaObject::getMimeType($media_path);
                if (in_array($mime, ["image/jpeg", "image/svg+xml", "image/gif", "image/png"])) {
                    $title = basename($media_path);
                    $html = $ui_renderer->render($ui_factory->image()->responsive($media_path, $title));
                } elseif (in_array($mime, ["video/mp4"])) {
                    $video = $ui_factory->player()->video($media_path);
                    $html = $ui_renderer->render($video);
                } elseif (in_array($mime, ["audio/mpeg"])) {
                    $audio = $ui_factory->player()->audio($media_path);
                    $html = $ui_renderer->render($audio);
                } else {
                    // download?
                    $html = "";
                }


                $tpl->setCurrentBlock("player");
                $tpl->setVariable(
                    "PLAYER",
                    $html
                );
                $tpl->parseCurrentBlock();
            }

            // access
            if ($enable_internal_rss && $item["visibility"] != "") {
                $obj_id = ilObject::_lookupObjId($item["ref_id"]);
                $tpl->setCurrentBlock("access");
                $tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
                if ($item["visibility"] === NEWS_PUBLIC ||
                    ((int) $item["priority"] === 0 &&
                    ilBlockSetting::_lookup(
                        "news",
                        "public_notifications",
                        0,
                        $obj_id
                    ))) {
                    $tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
                } else {
                    $tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
                }
                $tpl->parseCurrentBlock();
            }

            // content
            $renderer = ilNewsRendererFactory::getRenderer($item["context_obj_type"]);
            if (trim($item["content"]) != "") {		// content
                $it = new ilNewsItem($item["id"]);
                $renderer->setNewsItem($it, $item["ref_id"]);
                $tpl->setCurrentBlock("content");
                $tpl->setVariable("VAL_CONTENT", $renderer->getDetailContent());
                $tpl->parseCurrentBlock();
            }
            if ($item["update_date"] != $item["creation_date"]) {		// update date
                $tpl->setCurrentBlock("ni_update");
                $tpl->setVariable("TXT_LAST_UPDATE", $lng->txt("last_update"));
                $tpl->setVariable(
                    "VAL_LAST_UPDATE",
                    ilDatePresentation::formatDate(new ilDateTime($item["update_date"], IL_CAL_DATETIME))
                );
                $tpl->parseCurrentBlock();
            }

            // creation date
            if ($item["creation_date"] != "") {
                $tpl->setCurrentBlock("ni_update");
                $tpl->setVariable(
                    "VAL_CREATION_DATE",
                    ilDatePresentation::formatDate(new ilDateTime($item["creation_date"], IL_CAL_DATETIME))
                );
                $tpl->setVariable("TXT_CREATED", $lng->txt("created"));
                $tpl->parseCurrentBlock();
            }


            // context / title
            if ($news_context > 0) {
                //$obj_id = ilObject::_lookupObjId($_GET["news_context"]);
                $obj_id = ilObject::_lookupObjId($item["ref_id"]);
                $obj_type = ilObject::_lookupType($obj_id);
                $obj_title = ilObject::_lookupTitle($obj_id);

                // file hack, not nice
                if ($obj_type === "file") {
                    $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $item["ref_id"]);
                    $url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "sendfile");
                    $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->std_request->getRefId());

                    $button = ilLinkButton::getInstance();
                    $button->setUrl($url);
                    $button->setCaption("download");

                    $tpl->setCurrentBlock("download");
                    $tpl->setVariable("BUTTON_DOWNLOAD", $button->render());
                    $tpl->parseCurrentBlock();
                }

                // forum hack, not nice
                $add = "";
                if ($obj_type === "frm" && ($item["context_sub_obj_type"] ?? "") === "pos"
                    && $item["context_sub_obj_id"] > 0) {
                    $pos = $item["context_sub_obj_id"];
                    $thread = ilObjForumAccess::_getThreadForPosting($pos);
                    if ($thread > 0) {
                        $add = "_" . $thread . "_" . $pos;
                    }
                }

                // wiki hack, not nice
                if ($obj_type === "wiki" && $item["context_sub_obj_type"] === "wpg"
                    && $item["context_sub_obj_id"] > 0) {
                    $wptitle = ilWikiPage::lookupTitle($item["context_sub_obj_id"]);
                    if ($wptitle != "") {
                        $add = "_" . ilWikiUtil::makeUrlTitle($wptitle);
                    }
                }

                $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
                    $obj_type . "_" . $item["ref_id"] . $add;

                // lm page hack, not nice
                if (
                    ($item["context_sub_obj_type"] ?? "") === "pg" &&
                    $item["context_sub_obj_id"] > 0 &&
                    in_array($obj_type, ["lm"], true)) {
                    $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
                        "pg_" . $item["context_sub_obj_id"] . "_" . $item["ref_id"];
                }

                // blog posting hack, not nice
                if ($obj_type === "blog" && ($item["context_sub_obj_type"] ?? "") === "blp"
                    && $item["context_sub_obj_id"] > 0) {
                    $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
                        "blog_" . $item["ref_id"] . "_" . $item["context_sub_obj_id"];
                }

                $context_opened = false;
                $loc_stop = $item["loc_stop"] ?? "";
                if ($item["loc_context"] != null &&
                    $item["loc_context"] != $loc_stop
                ) {
                    $tpl->setCurrentBlock("context");
                    $context_opened = true;
                    $cont_loc = new ilLocatorGUI();
                    $cont_loc->addContextItems($item["loc_context"], true, (int) $loc_stop);
                    $tpl->setVariable("CONTEXT_LOCATOR", $cont_loc->getHTML());
                }

                //var_dump($item);
                $no_context_title = $item["no_context_title"] ?? false;
                if ($no_context_title !== true) {
                    if (!$context_opened) {
                        $tpl->setCurrentBlock("context");
                    }
                    $tpl->setVariable("HREF_CONTEXT_TITLE", $url_target);
                    $tpl->setVariable("CONTEXT_TITLE", $obj_title);
                    $tpl->setVariable("IMG_CONTEXT_TITLE", ilObject::_getIcon($obj_id, "big", $obj_type));
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
                    $item["context_obj_type"],
                    $item["title"],
                    $item["content_is_lang_var"],
                    $item["agg_ref_id"] ?? 0,
                    $item["aggregation"] ?? []
                )
            );


            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM_ROW_CSS", $row_css);
            $tpl->parseCurrentBlock();
        }

        $content = $tpl->get();
        if ($this->getProperty("title") != "") {
            $title = $this->getProperty("title");
        } else {
            $title = $lng->txt("news_internal_news");
        }
        $panel = $this->ui->factory()->panel()->standard($title, $this->ui->factory()->legacy($content));

        $parameter_name = 'news_page';

        $pagination = $this->ui->factory()->viewControl()->pagination()
                              ->withTargetURL($ilCtrl->getLinkTarget($this, "showNews"), "news_page")
                              ->withTotalEntries(count($this->getData()))
                              ->withPageSize(1)
                              ->withCurrentPage($curr_cnt - 1);
        $panel = $panel->withViewControls([$pagination]);

        return $this->ui->renderer()->render($panel);
    }

    protected function getMediaPath(int $mob_id): string
    {
        $media_path = "";
        if ($mob_id > 0) {
            $mob = new ilObjMediaObject($mob_id);
            $med = $mob->getMediaItem("Standard");
            if (strcasecmp("Reference", $med->getLocationType()) === 0) {
                $media_path = $med->getLocation();
            } else {
                $media_path = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
            }
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
        $data = $this->getNewsData();
        $this->setData($data);
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
        $data = $this->getNewsData();
        $this->setData($data);
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

        $std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

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
        $ilUser = $this->user;

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

            $cache = new ilNewsCache();
            $cache->deleteEntry($ilUser->getId() . ":" . $this->std_request->getRefId());

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

        $tpl = new ilTemplate("tpl.show_feed_url.html", true, true, "Services/News");
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
            $this->ui->factory()->legacy($tpl->get())
        );

        return $this->ui->renderer()->render($panel);
    }

    public function getDynamic(): bool
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        // @todo: enable js loading
        if ($this->new_rendering) {
            return false;
        }

        if ($ilCtrl->getCmd() === "hideNotifications" ||
            $ilCtrl->getCmd() === "showNotifications") {
            return false;
        }

        if ($ilCtrl->getCmdClass() !== "ilcolumngui" && $ilCtrl->getCmd() !== "enableJS") {
            $sess_feed_js = "";
            if (ilSession::get("il_feed_js") != "") {
                $sess_feed_js = ilSession::get("il_feed_js");
            }

            if ($sess_feed_js !== "n" &&
                ($ilUser->getPref("il_feed_js") !== "n" || $sess_feed_js === "y")) {
                // do not get feed dynamically, if cache hit is given.
                //				if (!$this->feed->checkCacheHit())
                //				{
                return true;
                //				}
            }
        }

        return false;
    }

    public function getDynamicReload(): string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilCtrl->setParameterByClass(
            "ilcolumngui",
            "block_id",
            "block_" . $this->getBlockType() . "_" . $this->getBlockId()
        );

        $rel_tpl = new ilTemplate("tpl.dynamic_reload.html", true, true, "Services/News");
        $rel_tpl->setVariable("TXT_LOADING", $lng->txt("news_loading_news"));
        $rel_tpl->setVariable("BLOCK_ID", "block_" . $this->getBlockType() . "_" . $this->getBlockId());
        $rel_tpl->setVariable(
            "TARGET",
            $ilCtrl->getLinkTargetByClass("ilcolumngui", "updateBlock", "", true)
        );

        // no JS
        $rel_tpl->setVariable("TXT_NEWS_CLICK_HERE", $lng->txt("news_no_js_click_here"));
        $rel_tpl->setVariable(
            "TARGET_NO_JS",
            $ilCtrl->getLinkTargetByClass(strtolower(get_class($this)), "disableJS")
        );

        return $rel_tpl->get();
    }

    public function getJSEnabler(): string
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass(
            "ilcolumngui",
            "block_id",
            "block_" . $this->getBlockType() . "_" . $this->getBlockId()
        );
        //echo "hh";
        $rel_tpl = new ilTemplate("tpl.js_enabler.html", true, true, "Services/News");
        $rel_tpl->setVariable("BLOCK_ID", "block_" . $this->getBlockType() . "_" . $this->getBlockId());
        $rel_tpl->setVariable(
            "TARGET",
            $ilCtrl->getLinkTargetByClass(strtolower(get_class($this)), "enableJS", "", true, false)
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

    //
    // New rendering
    //



    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        $info = $this->getInfoForData($data);

        $props = [
            $this->lng->txt("date") => $info["creation_date"]
        ];

        $factory = $this->ui->factory();
        $item = $factory->item()->standard($factory->link()->standard($info["news_title"], $info["url"]))
            ->withProperties($props);
        if ($info["ref_id"] > 0) {
            $item = $item->withDescription($info["type_txt"] . ": " . $info["obj_title"]);
        }
        return $item;
    }

    public function getNoItemFoundContent(): string
    {
        return $this->lng->txt("news_no_news_items");
    }
}
