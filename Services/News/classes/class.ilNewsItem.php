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

const NEWS_NOTICE = 0;
const NEWS_MESSAGE = 1;
const NEWS_WARNING = 2;

const NEWS_TEXT = "text";
const NEWS_HTML = "html";
const NEWS_AUDIO = "audio";
const NEWS_USERS = "users";
const NEWS_PUBLIC = "public";

/**
 * A news item can be created by different sources. E.g. when
 * a new forum posting is created, or when a change in a
 * learning module is announced.
 *
 * Please note that this class contains a lot of deprectated functions that
 * will be move to other classes in the future. Please avoid to use these functions. This class should
 * be a pure data class without persistence in the future.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsItem
{
    private int $mob_cnt_download = 0;
    protected int $mob_cnt_play = 0;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilAccessHandler $access;
    protected ilObjectDataCache $obj_data_cache;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected int $id = 0;
    protected string $title = "";
    protected string $content = "";
    protected bool $content_html = false;
    protected int $context_obj_id = 0;
    protected string $context_obj_type = "";
    protected int $context_sub_obj_id = 0;
    protected ?string $context_sub_obj_type = null;
    protected string $content_type = "text";
    protected string $creation_date = "";
    protected string $update_date = "";
    protected int $user_id = 0;
    protected int $update_user_id = 0;
    protected string $visibility = "users";
    protected string $content_long = "";
    protected int $priority = 1;
    protected bool $content_is_lang_var = false;
    protected int $mob_id = 0;
    protected string $playtime = "";
    private static int $privFeedId = 0;
    private bool $limitation = false;
    protected bool $content_text_is_lang_var = false;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(int $a_id = 0)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
        $this->limitation = true;
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setContent(string $a_content): void
    {
        $this->content = $a_content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContextObjId(int $a_context_obj_id): void
    {
        $this->context_obj_id = $a_context_obj_id;
    }

    public function getContextObjId(): int
    {
        return $this->context_obj_id;
    }

    public function setContextObjType(string $a_context_obj_type): void
    {
        $this->context_obj_type = $a_context_obj_type;
    }

    public function getContextObjType(): string
    {
        return $this->context_obj_type;
    }

    public function setContextSubObjId(int $a_context_sub_obj_id): void
    {
        $this->context_sub_obj_id = $a_context_sub_obj_id;
    }

    public function getContextSubObjId(): int
    {
        return $this->context_sub_obj_id;
    }

    public function setContextSubObjType(?string $a_context_sub_obj_type): void
    {
        $this->context_sub_obj_type = $a_context_sub_obj_type;
    }

    public function getContextSubObjType(): ?string
    {
        return $this->context_sub_obj_type;
    }

    public function setContentType(string $a_content_type = "text"): void
    {
        $this->content_type = $a_content_type;
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function setCreationDate(string $a_creation_date): void
    {
        $this->creation_date = $a_creation_date;
    }

    public function getCreationDate(): string
    {
        return $this->creation_date;
    }

    public function setUpdateDate(string $a_update_date): void
    {
        $this->update_date = $a_update_date;
    }

    public function getUpdateDate(): string
    {
        return $this->update_date;
    }

    public function setUserId(int $a_user_id): void
    {
        $this->user_id = $a_user_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUpdateUserId(int $a_val): void
    {
        $this->update_user_id = $a_val;
    }

    public function getUpdateUserId(): int
    {
        return $this->update_user_id;
    }

    /**
     * @param	string	$a_visibility	Access level of news.
     */
    public function setVisibility(
        string $a_visibility = "users"
    ): void {
        $this->visibility = $a_visibility;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @param	string	$a_content_long	Long content of news
     */
    public function setContentLong(string $a_content_long): void
    {
        $this->content_long = $a_content_long;
    }

    public function getContentLong(): string
    {
        return $this->content_long;
    }

    public function setPriority(int $a_priority = 1): void
    {
        $this->priority = $a_priority;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setContentIsLangVar(
        bool $a_content_is_lang_var = false
    ): void {
        $this->content_is_lang_var = $a_content_is_lang_var;
    }

    public function getContentIsLangVar(): bool
    {
        return $this->content_is_lang_var;
    }

    public function setMobId(int $a_mob_id): void
    {
        $this->mob_id = $a_mob_id;
    }

    public function getMobId(): int
    {
        return $this->mob_id;
    }

    /**
     * @param	string	$a_playtime	Play Time, hh:mm:ss (of attached media file)
     */
    public function setPlaytime(string $a_playtime): void
    {
        $this->playtime = $a_playtime;
    }

    public function getPlaytime(): string
    {
        return $this->playtime;
    }

    /**
     * Set Limitation for number of items.
     */
    public function setLimitation(bool $a_limitation): void
    {
        $this->limitation = $a_limitation;
    }

    public function getLimitation(): bool
    {
        return $this->limitation;
    }

    public function setContentTextIsLangVar(bool $a_val = false): void
    {
        $this->content_text_is_lang_var = $a_val;
    }

    public function getContentTextIsLangVar(): bool
    {
        return $this->content_text_is_lang_var;
    }

    public function setMobPlayCounter(int $a_val): void
    {
        $this->mob_cnt_play = $a_val;
    }

    public function getMobPlayCounter(): int
    {
        return $this->mob_cnt_play;
    }

    public function setMobDownloadCounter(int $a_val): void
    {
        $this->mob_cnt_download = $a_val;
    }

    public function getMobDownloadCounter(): int
    {
        return $this->mob_cnt_download;
    }

    public function setContentHtml(bool $a_val): void
    {
        $this->content_html = $a_val;
    }

    public function getContentHtml(): bool
    {
        return $this->content_html;
    }

    /**
     * Read item from database.
     * @deprecated (will migrate to ilNewsData or other class taking care of persistence)
     */
    public function read(): void
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM il_news_item WHERE id = " .
            $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setTitle((string) $rec["title"]);
            $this->setContent((string) $rec["content"]);
            $this->setContextObjId((int) $rec["context_obj_id"]);
            $this->setContextObjType($rec["context_obj_type"]);
            $this->setContextSubObjId((int) $rec["context_sub_obj_id"]);
            $this->setContextSubObjType((string) $rec["context_sub_obj_type"]);
            $this->setContentType((string) $rec["content_type"]);
            $this->setCreationDate((string) $rec["creation_date"]);
            $this->setUpdateDate((string) $rec["update_date"]);
            $this->setUserId((int) $rec["user_id"]);
            $this->setUpdateUserId((int) $rec["update_user_id"]);
            $this->setVisibility((string) $rec["visibility"]);
            $this->setContentLong((string) $rec["content_long"]);
            $this->setPriority((int) $rec["priority"]);
            $this->setContentIsLangVar((bool) $rec["content_is_lang_var"]);
            $this->setContentTextIsLangVar((bool) $rec["content_text_is_lang_var"]);
            $this->setMobId((int) $rec["mob_id"]);
            $this->setPlaytime((string) $rec["playtime"]);
            $this->setMobPlayCounter((int) $rec["mob_cnt_play"]);
            $this->setMobDownloadCounter((int) $rec["mob_cnt_download"]);
            $this->setContentHtml((bool) $rec["content_html"]);
        }
    }

    /**
     * Create
     * @deprecated (will migrate to ilNewsData or other class taking care of persistence)
     */
    public function create(): void
    {
        $ilDB = $this->db;

        // insert new record into db
        $this->setId($ilDB->nextId("il_news_item"));
        $ilDB->insert("il_news_item", [
            "id" => ["integer", $this->getId()],
            "title" => ["text", $this->getTitle()],
            "content" => ["clob", $this->getContent()],
            "content_html" => ["integer", (int) $this->getContentHtml()],
            "context_obj_id" => ["integer", $this->getContextObjId()],
            "context_obj_type" => ["text", $this->getContextObjType()],
            "context_sub_obj_id" => ["integer", $this->getContextSubObjId()],
            "context_sub_obj_type" => ["text", $this->getContextSubObjType()],
            "content_type" => ["text", $this->getContentType()],
            "creation_date" => ["timestamp", ilUtil::now()],
            "update_date" => ["timestamp", ilUtil::now()],
            "user_id" => ["integer", $this->getUserId()],
            "update_user_id" => ["integer", $this->getUpdateUserId()],
            "visibility" => ["text", $this->getVisibility()],
            "content_long" => ["clob", $this->getContentLong()],
            "priority" => ["integer", $this->getPriority()],
            "content_is_lang_var" => ["integer", $this->getContentIsLangVar()],
            "content_text_is_lang_var" => ["integer", (int) $this->getContentTextIsLangVar()],
            "mob_id" => ["integer", $this->getMobId()],
            "playtime" => ["text", $this->getPlaytime()]
        ]);


        $news_set = new ilSetting("news");
        $max_items = $news_set->get("max_items");
        if ($max_items <= 0) {
            $max_items = 50;
        }

        // limit number of news
        if ($this->getLimitation()) {
            // Determine how many rows should be deleted
            $query = "SELECT count(*) cnt " .
                "FROM il_news_item " .
                "WHERE " .
                    "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                    " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                    " AND context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
                    " AND " . $ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true) . " ";

            $set = $ilDB->query($query);
            $rec = $ilDB->fetchAssoc($set);

            // if we have more records than allowed, delete them
            if (($rec["cnt"] > $max_items) && $this->getContextObjId() > 0) {
                $query = "SELECT * " .
                    "FROM il_news_item " .
                    "WHERE " .
                        "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                        " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                        " AND context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
                        " AND " . $ilDB->equals("context_sub_obj_type", $this->getContextSubObjType(), "text", true) .
                        " ORDER BY creation_date ASC";

                $ilDB->setLimit($rec["cnt"] - $max_items, 0);
                $del_set = $ilDB->query($query);
                while ($del_item = $ilDB->fetchAssoc($del_set)) {
                    $del_news = new ilNewsItem((int) $del_item["id"]);
                    $del_news->delete();
                }
            }
        }
    }

    /**
     * Update item in database
     *
     * @deprecated (will migrate to ilNewsData or other class taking care of persistence)
     * @param bool $a_as_new If true, creation date is set "now"
     */
    public function update(bool $a_as_new = false): void
    {
        $ilDB = $this->db;

        $fields = [
            "title" => ["text", $this->getTitle()],
            "content" => ["clob", $this->getContent()],
            "content_html" => ["integer", (int) $this->getContentHtml()],
            "context_obj_id" => ["integer", $this->getContextObjId()],
            "context_obj_type" => ["text", $this->getContextObjType()],
            "context_sub_obj_id" => ["integer", $this->getContextSubObjId()],
            "context_sub_obj_type" => ["text", $this->getContextSubObjType()],
            "content_type" => ["text", $this->getContentType()],
            "user_id" => ["integer", $this->getUserId()],
            "update_user_id" => ["integer", $this->getUpdateUserId()],
            "visibility" => ["text", $this->getVisibility()],
            "content_long" => ["clob", $this->getContentLong()],
            "priority" => ["integer", $this->getPriority()],
            "content_is_lang_var" => ["integer", $this->getContentIsLangVar()],
            "content_text_is_lang_var" => ["integer", (int) $this->getContentTextIsLangVar()],
            "mob_id" => ["integer", $this->getMobId()],
            "mob_cnt_play" => ["integer", $this->getMobPlayCounter()],
            "mob_cnt_download" => ["integer", $this->getMobDownloadCounter()],
            "playtime" => ["text", $this->getPlaytime()]
        ];

        $now = ilUtil::now();
        if ($a_as_new) {
            $fields["creation_date"] = ["timestamp", $now];
        }
        $fields["update_date"] = ["timestamp", $now];

        $ilDB->update("il_news_item", $fields, [
            "id" => ["integer", $this->getId()]
        ]);
    }


    /**
     * Get all news items for a user.
     * @deprecated (will migrate to ilNewsData)
     */
    public static function _getNewsItemsOfUser(
        int $a_user_id,
        bool $a_only_public = false,
        bool $a_prevent_aggregation = false,
        int $a_per = 0,
        array &$a_cnt = []
    ): array {
        global $DIC;

        $ilAccess = $DIC->access();

        $fav_rep = new ilFavouritesDBRepository();

        $news_item = new ilNewsItem();

        $per = $a_per;

        // this is currently not used
        $ref_ids = [];

        if (ilObjUser::_lookupPref($a_user_id, "pd_items_news") !== "n") {
            // get all items of the personal desktop
            $pd_items = $fav_rep->getFavouritesOfUser($a_user_id);
            foreach ($pd_items as $item) {
                if (!in_array($item["ref_id"], $ref_ids)) {
                    $ref_ids[] = (int) $item["ref_id"];
                }
            }

            // get all memberships
            $crs_mbs = ilParticipants::_getMembershipByType($a_user_id, ['crs']);
            $grp_mbs = ilParticipants::_getMembershipByType($a_user_id, ['grp']);
            $items = array_merge($crs_mbs, $grp_mbs);
            foreach ($items as $i) {
                $item_references = ilObject::_getAllReferences($i);
                if (is_array($item_references) && count($item_references)) {
                    foreach ($item_references as $ref_id) {
                        if (!in_array($ref_id, $ref_ids)) {
                            $ref_ids[] = $ref_id;
                        }
                    }
                }
            }
        }

        $data = [];

        foreach ($ref_ids as $ref_id) {
            if (!$a_only_public) {
                // this loop should not cost too much performance
                $acc = $ilAccess->checkAccessOfUser($a_user_id, "read", "", $ref_id);

                if (!$acc) {
                    continue;
                }
            }
            if (self::getPrivateFeedId() > 0) {
                global $DIC;

                $rbacsystem = $DIC->rbac()->system();
                $acc = $rbacsystem->checkAccessOfUser(self::getPrivateFeedId(), "read", $ref_id);

                if (!$acc) {
                    continue;
                }
            }

            $obj_id = ilObject::_lookupObjId($ref_id);
            $obj_type = ilObject::_lookupType($obj_id);
            $news = $news_item->getNewsForRefId(
                $ref_id,
                $a_only_public,
                false,
                $per,
                $a_prevent_aggregation,
                false,
                false,
                false,
                $a_user_id
            );

            // counter
            if (!is_null($a_cnt)) {
                $a_cnt[$ref_id] = count($news);
            }

            $data = self::mergeNews($data, $news);
        }

        $data = ilArrayUtil::sortArray($data, "creation_date", "desc", false, true);

        return $data;
    }

    /**
     * Get News For Ref Id.
     *
     * @deprecated (will migrate to ilNewsData)
     * @param int $a_limit currently only supported for groups and courses
     * @param int[] $a_excluded currently only supported for groups and courses (news ids)
     */
    public function getNewsForRefId(
        int $a_ref_id,
        bool $a_only_public = false,
        bool $a_stopnesting = false,
        int $a_time_period = 0,
        bool $a_prevent_aggregation = true,
        bool $a_forum_group_sequences = false,
        bool $a_no_auto_generated = false,
        bool $a_ignore_date_filter = false,
        int $a_user_id = null,
        int $a_limit = 0,
        array $a_excluded = []
    ): array {
        $obj_id = ilObject::_lookupObjId($a_ref_id);
        $obj_type = ilObject::_lookupType($obj_id);

        // get starting date
        $starting_date = "";
        if ($obj_type === "grp" || $obj_type === "crs") {
            // see #31471, #30687, and ilMembershipNotification
            if (!ilContainer::_lookupContainerSetting(
                $obj_id,
                'cont_use_news',
                '1'
            ) || (
                !ilContainer::_lookupContainerSetting(
                    $obj_id,
                    'cont_show_news',
                    '1'
                ) && !ilContainer::_lookupContainerSetting(
                    $obj_id,
                    'news_timeline'
                )
            )) {
                return [];
            }

            $hide_news_per_date = ilBlockSetting::_lookup(
                "news",
                "hide_news_per_date",
                0,
                $obj_id
            );
            if ($hide_news_per_date && !$a_ignore_date_filter) {
                $starting_date = ilBlockSetting::_lookup(
                    "news",
                    "hide_news_date",
                    0,
                    $obj_id
                );
            }
        }

        if ($obj_type === "cat" && !$a_stopnesting) {
            $news = $this->getAggregatedChildNewsData(
                $a_ref_id,
                $a_only_public,
                $a_time_period,
                $a_prevent_aggregation,
                $starting_date,
                $a_no_auto_generated
            );
        } elseif (($obj_type === "grp" || $obj_type === "crs") &&
            !$a_stopnesting) {
            $news = $this->getAggregatedNewsData(
                $a_ref_id,
                $a_only_public,
                $a_time_period,
                $a_prevent_aggregation,
                $starting_date,
                $a_no_auto_generated,
                $a_user_id,
                $a_limit,
                $a_excluded
            );
        } else {
            $news_item = new ilNewsItem();
            $news_item->setContextObjId($obj_id);
            $news_item->setContextObjType($obj_type);
            $news = $news_item->queryNewsForContext(
                $a_only_public,
                $a_time_period,
                $starting_date,
                $a_no_auto_generated
            );
            $unset = [];
            foreach ($news as $k => $v) {
                if (!$a_only_public || $v["visibility"] == NEWS_PUBLIC ||
                    ($v["priority"] == 0 &&
                        ilBlockSetting::_lookup(
                            "news",
                            "public_notifications",
                            0,
                            $obj_id
                        ))) {
                    $news[$k]["ref_id"] = $a_ref_id;
                } else {
                    $unset[] = $k;
                }
            }
            foreach ($unset as $un) {
                unset($news[$un]);
            }
        }

        if (!$a_prevent_aggregation) {
            $news = $this->aggregateForums($news);
        } elseif ($a_forum_group_sequences) {
            $news = $this->aggregateForums($news, true);
        }

        return $news;
    }

    /**
     * Get news aggregation (e.g. for courses, groups)
     * @deprecated (will migrate to ilNewsData)
     */
    public function getAggregatedNewsData(
        int $a_ref_id,
        bool $a_only_public = false,
        int $a_time_period = 0,
        bool $a_prevent_aggregation = false,
        string $a_starting_date = "",
        bool $a_no_auto_generated = false,
        int $a_user_id = null,
        int $a_limit = 0,
        array $a_exclude = []
    ): array {
        $tree = $this->tree;
        $ilAccess = $this->access;
        $ilObjDataCache = $this->obj_data_cache;

        // get news of parent object
        $data = [];

        // get subtree
        $cur_node = $tree->getNodeData($a_ref_id);

        // do not check for lft (materialized path)
        if ($cur_node) {
            $nodes = $tree->getSubTree($cur_node, true);
        } else {
            $nodes = [];
        }

        // preload object data cache
        $ref_ids = [];
        $obj_ids = [];
        $ref_id = [];
        foreach ($nodes as $node) {
            $ref_ids[] = (int) $node["child"];
            $obj_ids[] = (int) $node["obj_id"];
        }

        $ilObjDataCache->preloadReferenceCache($ref_ids);
        if (!$a_only_public) {
            ilObjectActivation::preloadData($ref_ids);
        }

        // no check, for which of the objects any news are available
        $news_obj_ids = self::filterObjIdsPerNews($obj_ids, $a_time_period, $a_starting_date);
        //$news_obj_ids = $obj_ids;

        // get news for all subtree nodes
        $contexts = [];
        foreach ($nodes as $node) {
            // only go on, if news are available
            if (!in_array($node["obj_id"], $news_obj_ids)) {
                continue;
            }

            if (!$a_only_public) {
                if (!$a_user_id) {
                    $acc = $ilAccess->checkAccess("read", "", (int) $node["child"]);
                } else {
                    $acc = $ilAccess->checkAccessOfUser(
                        $a_user_id,
                        "read",
                        "",
                        (int) $node["child"]
                    );
                }
                if (!$acc) {
                    continue;
                }
            }

            $ref_id[$node["obj_id"]] = $node["child"];
            $contexts[] = [
                "obj_id" => $node["obj_id"],
                "obj_type" => $node["type"]
            ];
        }

        // sort and return
        $news = $this->queryNewsForMultipleContexts(
            $contexts,
            $a_only_public,
            $a_time_period,
            $a_starting_date,
            $a_no_auto_generated,
            $a_user_id,
            $a_limit,
            $a_exclude
        );

        $to_del = [];
        foreach ($news as $k => $v) {
            $news[$k]["ref_id"] = $ref_id[$v["context_obj_id"]];
        }

        $data = self::mergeNews($data, $news);
        $data = ilArrayUtil::sortArray($data, "creation_date", "desc", false, true);

        if (!$a_prevent_aggregation) {
            $data = $this->aggregateFiles($data, $a_ref_id);
        }

        return $data;
    }

    /**
     * @deprecated will move to ilNewsData
     */
    protected function aggregateForums(
        array $news,
        bool $a_group_posting_sequence = false
    ): array {
        $to_del = [];
        $forums = [];
        $last_aggregation_forum = 0;

        // aggregate
        foreach ($news as $k => $v) {
            if ($a_group_posting_sequence && $last_aggregation_forum > 0 &&
                $last_aggregation_forum != $v["context_obj_id"]) {
                $forums[$last_aggregation_forum] = "";
            }

            if ($v["context_obj_type"] === "frm") {
                if (!isset($forums[$v["context_obj_id"]])) {
                    // $forums[forum_id] = news_id;
                    $forums[$v["context_obj_id"]] = $k;
                    $last_aggregation_forum = $v["context_obj_id"];
                } else {
                    $to_del[] = $k;
                }

                $news[$k]["no_context_title"] = true;

                // aggregate every forum into it's "k" news
                $news[$forums[$news[$k]["context_obj_id"]]]["aggregation"][$k]
                    = $news[$k];
                $news[$k]["agg_ref_id"]
                    = $news[$k]["ref_id"];
                $news[$k]["content"] = "";
                $news[$k]["content_long"] = "";
            }
        }

        // delete double entries
        foreach ($to_del as $k) {
            unset($news[$k]);
        }
        //var_dump($news[14]["aggregation"]);


        return $news;
    }

    /**
     * @deprecated will move to ilNewsData
     */
    protected function aggregateFiles(
        array $news,
        int $a_ref_id
    ): array {
        $first_file = "";
        $to_del = [];
        foreach ($news as $k => $v) {
            // aggregate file related news
            if ($v["context_obj_type"] === "file") {
                if ($first_file === "") {
                    $first_file = $k;
                } else {
                    $to_del[] = $k;
                }
                $news[$first_file]["aggregation"][$k] = $v;
                $news[$first_file]["agg_ref_id"] = $a_ref_id;
                $news[$first_file]["ref_id"] = $a_ref_id;
            }
        }

        foreach ($to_del as $v) {
            unset($news[$v]);
        }

        return $news;
    }


    /**
     * Get news aggregation for child objects (e.g. for categories)
     * @deprecated will move to ilNewsData
     */
    protected function getAggregatedChildNewsData(
        int $a_ref_id,
        bool $a_only_public = false,
        int $a_time_period = 0,
        bool $a_prevent_aggregation = false,
        string $a_starting_date = "",
        bool $a_no_auto_generated = false
    ): array {
        $tree = $this->tree;
        $ilAccess = $this->access;
        $ref_id = [];
        // get news of parent object
        $data = $this->getNewsForRefId(
            $a_ref_id,
            $a_only_public,
            true,
            $a_time_period,
            true,
            false,
            false,
            $a_no_auto_generated
        );
        foreach ($data as $k => $v) {
            $data[$k]["ref_id"] = $a_ref_id;
        }

        // get childs
        $nodes = $tree->getChilds($a_ref_id);

        // no check, for which of the objects any news are available
        $obj_ids = [];
        foreach ($nodes as $node) {
            $obj_ids[] = $node["obj_id"];
        }
        $news_obj_ids = self::filterObjIdsPerNews($obj_ids, $a_time_period, $a_starting_date);
        //$news_obj_ids = $obj_ids;

        // get news for all subtree nodes
        $contexts = [];
        foreach ($nodes as $node) {
            // only go on, if news are available
            if (!in_array($node["obj_id"], $news_obj_ids)) {
                continue;
            }

            if (!$a_only_public && !$ilAccess->checkAccess("read", "", (int) $node["child"])) {
                continue;
            }
            $ref_id[$node["obj_id"]] = $node["child"];
            $contexts[] = [
                "obj_id" => $node["obj_id"],
                "obj_type" => $node["type"]
            ];
        }

        $news = $this->queryNewsForMultipleContexts(
            $contexts,
            $a_only_public,
            $a_time_period,
            $a_starting_date,
            $a_no_auto_generated
        );
        foreach ($news as $k => $v) {
            $news[$k]["ref_id"] = $ref_id[$v["context_obj_id"]];
        }
        $data = self::mergeNews($data, $news);

        // sort and return
        $data = ilArrayUtil::sortArray($data, "creation_date", "desc", false, true);

        if (!$a_prevent_aggregation) {
            $data = $this->aggregateFiles($data, $a_ref_id);
        }

        return $data;
    }

    /**
     * Set context for news
     */
    public function setContext(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id = 0,
        string $a_sub_obj_type = ""
    ): void {
        $this->setContextObjId($a_obj_id);
        $this->setContextObjType($a_obj_type);
        $this->setContextSubObjId($a_sub_obj_id);
        $this->setContextSubObjType($a_sub_obj_type);
    }

    /**
     * Convert time period for DB-queries
     * @param string|int $a_time_period
     */
    protected static function handleTimePeriod($a_time_period): string
    {
        // time period is number of days
        if (is_numeric($a_time_period)) {
            if ($a_time_period > 0) {
                return date('Y-m-d H:i:s', time() - ($a_time_period * 24 * 60 * 60));
            }
        }
        // time period is datetime
        elseif (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $a_time_period)) {
            return $a_time_period;
        }
        return "";
    }

    /**
     * Query news for a context
     * @deprecated will move to ilNewsData
     */
    public function queryNewsForContext(
        bool $a_for_rss_use = false,
        int $a_time_period = 0,
        string $a_starting_date = "",
        bool $a_no_auto_generated = false,
        bool $a_oldest_first = false,
        int $a_limit = 0
    ): array {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $and = "";
        if ($a_time_period > 0) {
            $limit_ts = self::handleTimePeriod($a_time_period);
            $and = " AND creation_date >= " . $ilDB->quote($limit_ts, "timestamp") . " ";
        }

        if ($a_starting_date !== "") {
            $and .= " AND creation_date > " . $ilDB->quote($a_starting_date, "timestamp") . " ";
        }

        if ($a_no_auto_generated) {
            $and .= " AND priority = 1 AND content_type = " . $ilDB->quote("text", "text") . " ";
        }

        // this is changed with 4.1 (news table for lm pages)
        if ($this->getContextSubObjId() > 0) {
            $and .= " AND context_sub_obj_id = " . $ilDB->quote($this->getContextSubObjId(), "integer") .
                " AND context_sub_obj_type = " . $ilDB->quote($this->getContextSubObjType(), "text");
        }

        $ordering = ($a_oldest_first)
            ? " creation_date ASC, id ASC "
            : " creation_date DESC, id DESC ";

        if ($a_for_rss_use && self::getPrivateFeedId() === 0) {
            $query = "SELECT * " .
                "FROM il_news_item " .
                " WHERE " .
                    "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                    " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                    $and .
                    " ORDER BY " . $ordering;
        } elseif (self::getPrivateFeedId() > 0) {
            $query = "SELECT il_news_item.* " .
                ", il_news_read.user_id user_read " .
                "FROM il_news_item LEFT JOIN il_news_read " .
                "ON il_news_item.id = il_news_read.news_id AND " .
                " il_news_read.user_id = " . $ilDB->quote(self::getPrivateFeedId(), "integer") .
                " WHERE " .
                    "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                    " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                    $and .
                    " ORDER BY " . $ordering;
        } else {
            $query = "SELECT il_news_item.* " .
                ", il_news_read.user_id as user_read " .
                "FROM il_news_item LEFT JOIN il_news_read " .
                "ON il_news_item.id = il_news_read.news_id AND " .
                " il_news_read.user_id = " . $ilDB->quote($ilUser->getId(), "integer") .
                " WHERE " .
                    "context_obj_id = " . $ilDB->quote($this->getContextObjId(), "integer") .
                    " AND context_obj_type = " . $ilDB->quote($this->getContextObjType(), "text") .
                    $and .
                    " ORDER BY " . $ordering;
        }
        //echo $query;
        $set = $ilDB->query($query);
        $result = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($a_limit > 0 && count($result) >= $a_limit) {
                continue;
            }
            if (!$a_for_rss_use || (self::getPrivateFeedId() > 0) || ($rec["visibility"] === NEWS_PUBLIC ||
                ((int) $rec["priority"] === 0 &&
                ilBlockSetting::_lookup(
                    "news",
                    "public_notifications",
                    0,
                    (int) $rec["context_obj_id"]
                )))) {
                $result[$rec["id"]] = $rec;
            }
        }

        // do we get data for rss and may the time limit by an issue?
        // do a second query without time limit.
        // this is not very performant, but I do not have a better
        // idea. The keep_rss_min setting is currently (Jul 2012) only set
        // by mediacasts
        if ($a_time_period && $a_for_rss_use) {
            $keep_rss_min = ilBlockSetting::_lookup(
                "news",
                "keep_rss_min",
                0,
                $this->getContextObjId()
            );
            if ($keep_rss_min > 0) {
                return $this->queryNewsForContext(
                    true,
                    0,
                    $a_starting_date,
                    $a_no_auto_generated,
                    $a_oldest_first,
                    (int) $keep_rss_min
                );
            }
        }

        return $result;
    }

    /**
     * Query news data by news ids
     * @param int[] $a_news_ids
     * @return array[]
     */
    public static function queryNewsByIds(array $a_news_ids): array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $news = [];
        $set = $ilDB->query("SELECT * FROM il_news_item " .
            " WHERE " . $ilDB->in("id", $a_news_ids, false, "integer"));
        while ($rec = $ilDB->fetchAssoc($set)) {
            $news[$rec["id"]] = $rec;
        }
        return $news;
    }

    /**
     * @deprecated will move to ilNewsData
     * @return int[]
     */
    public function checkNewsExistsForObjects(
        array $objects,
        int $a_time_period = 1
    ): array {
        $ilDB = $this->db;

        $all = [];

        $limit_ts = self::handleTimePeriod($a_time_period);

        // are there any news items for relevant objects and?
        $query = $ilDB->query("SELECT id,context_obj_id,context_obj_type" .
            " FROM il_news_item" .
            " WHERE " . $ilDB->in("context_obj_id", array_keys($objects), false, "integer") .
            " AND creation_date >= " . $ilDB->quote($limit_ts, "timestamp"));
        while ($rec = $ilDB->fetchAssoc($query)) {
            if ($objects[$rec["context_obj_id"]]["type"] == $rec["context_obj_type"]) {
                $all[] = (int) $rec["id"];
            }
        }

        return $all;
    }

    /**
     * @deprecated will move to ilNewsData
     */
    public function queryNewsForMultipleContexts(
        array $a_contexts,
        bool $a_for_rss_use = false,
        int $a_time_period = 0,
        string $a_starting_date = "",
        bool $a_no_auto_generated = false,
        int $a_user_id = null,
        int $a_limit = 0,
        array $a_exclude = []
    ): array {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $and = "";
        if ($a_time_period > 0) {
            $limit_ts = self::handleTimePeriod($a_time_period);
            $and = " AND creation_date >= " . $ilDB->quote($limit_ts, "timestamp") . " ";
        }

        if ($a_starting_date !== "") {
            $and .= " AND creation_date > " . $ilDB->quote($a_starting_date, "timestamp") . " ";
        }

        if ($a_no_auto_generated) {
            $and .= " AND priority = 1 AND content_type = " . $ilDB->quote("text", "text") . " ";
        }

        if ($a_limit > 0) {
            $ilDB->setLimit($a_limit, 0);
        }

        if (is_array($a_exclude) && count($a_exclude) > 0) {
            $and .= " AND " . $ilDB->in("id", $a_exclude, true, "integer") . " ";
        }

        $ids = [];
        $type = [];

        foreach ($a_contexts as $cont) {
            $ids[] = $cont["obj_id"];
            $type[$cont["obj_id"]] = $cont["obj_type"];
        }

        if ($a_for_rss_use && self::getPrivateFeedId() === 0) {
            $query = "SELECT * " .
                "FROM il_news_item " .
                " WHERE " .
                    $ilDB->in("context_obj_id", $ids, false, "integer") . " " .
                    $and .
                    " ORDER BY creation_date DESC ";
        } elseif (self::getPrivateFeedId() > 0) {
            $query = "SELECT il_news_item.* " .
                ", il_news_read.user_id as user_read " .
                "FROM il_news_item LEFT JOIN il_news_read " .
                "ON il_news_item.id = il_news_read.news_id AND " .
                " il_news_read.user_id = " . $ilDB->quote(self::getPrivateFeedId(), "integer") .
                " WHERE " .
                    $ilDB->in("context_obj_id", $ids, false, "integer") . " " .
                    $and .
                    " ORDER BY creation_date DESC ";
        } else {
            if ($a_user_id) {
                $user_id = $a_user_id;
            } else {
                $user_id = $ilUser->getId();
            }
            $query = "SELECT il_news_item.* " .
                ", il_news_read.user_id as user_read " .
                "FROM il_news_item LEFT JOIN il_news_read " .
                "ON il_news_item.id = il_news_read.news_id AND " .
                " il_news_read.user_id = " . $ilDB->quote($user_id, "integer") .
                " WHERE " .
                    $ilDB->in("context_obj_id", $ids, false, "integer") . " " .
                    $and .
                    " ORDER BY creation_date DESC ";
        }

        $set = $ilDB->query($query);
        $result = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($type[$rec["context_obj_id"]] == $rec["context_obj_type"]) {
                if (!$a_for_rss_use || self::getPrivateFeedId() > 0 || ($rec["visibility"] === NEWS_PUBLIC ||
                    ((int) $rec["priority"] === 0 &&
                    ilBlockSetting::_lookup(
                        "news",
                        "public_notifications",
                        0,
                        (int) $rec["context_obj_id"]
                    )))) {
                    $result[$rec["id"]] = $rec;
                }
            }
        }

        return $result;
    }


    /**
     * Set item read.
     * @deprecated will move to ilNewsData
     */
    public static function _setRead(
        int $a_user_id,
        int $a_news_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();
        $ilAppEventHandler = $DIC["ilAppEventHandler"];

        $ilDB->replace(
            "il_news_read",
            [
                "user_id" => ["integer", $a_user_id],
                "news_id" => ["integer", $a_news_id]
            ],
            []
        );

        $ilAppEventHandler->raise(
            "Services/News",
            "readNews",
            ["user_id" => $a_user_id, "news_ids" => [$a_news_id]]
        );
    }

    /**
     * Set item unread.
     * @deprecated will move to ilNewsData
     */
    public static function _setUnread(
        int $a_user_id,
        int $a_news_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();
        $ilAppEventHandler = $DIC["ilAppEventHandler"];

        $ilDB->manipulate("DELETE FROM il_news_read (user_id, news_id) VALUES (" .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND news_id = " . $ilDB->quote($a_news_id, "integer"));

        $ilAppEventHandler->raise(
            "Services/News",
            "unreadNews",
            ["user_id" => $a_user_id, "news_ids" => [$a_news_id]]
        );
    }

    /**
     * Merges two sets of news
     * @deprecated will move to ilNewsData
     */
    public static function mergeNews(
        array $n1,
        array $n2
    ): array {
        foreach ($n2 as $id => $news) {
            $n1[$id] = $news;
        }

        return $n1;
    }

    /**
     * Get default visibility for reference id
     * @deprecated will move to ilNewsData
     */
    public static function _getDefaultVisibilityForRefId(int $a_ref_id): string
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $news_set = new ilSetting("news");
        $default_visibility = ($news_set->get("default_visibility") != "")
                ? $news_set->get("default_visibility")
                : "users";

        if ($tree->isInTree($a_ref_id)) {
            $path = $tree->getPathFull($a_ref_id);

            foreach ($path as $key => $row) {
                if (!in_array($row["type"], ["root", "cat", "crs", "fold", "grp"], true)) {
                    continue;
                }

                $visibility = ilBlockSetting::_lookup(
                    "news",
                    "default_visibility",
                    0,
                    (int) $row["obj_id"]
                );

                if ($visibility != "") {
                    $default_visibility = $visibility;
                }
            }
        }

        return $default_visibility;
    }


    /**
     * Delete news item
     * @deprecated will move to ilNewsData
     */
    public function delete(): void
    {
        $ilDB = $this->db;

        // delete il_news_read entries
        $ilDB->manipulate("DELETE FROM il_news_read " .
            " WHERE news_id = " . $ilDB->quote($this->getId(), "integer"));

        // delete multimedia object
        $mob = $this->getMobId();

        // delete
        $query = "DELETE FROM il_news_item" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        // delete mob after news, to have a "mob usage" of 0
        if ($mob > 0 && ilObject::_exists($mob)) {
            $mob = new ilObjMediaObject($mob);
            $mob->delete();
        }
    }

    /**
     * Get all news of a context
     * @deprecated will move to ilNewsData
     * @return ilNewsItem[]
     */
    public static function getNewsOfContext(
        int $a_context_obj_id,
        string $a_context_obj_type,
        int $a_context_sub_obj_id = 0,
        string $a_context_sub_obj_type = ""
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $and = "";

        if ($a_context_obj_id === 0 || $a_context_obj_type === "") {
            return [];
        }

        if ($a_context_sub_obj_id > 0) {
            $and = " AND context_sub_obj_id = " . $ilDB->quote($a_context_sub_obj_id, "integer") .
                " AND context_sub_obj_type = " . $ilDB->quote($a_context_sub_obj_type, "text");
        }

        // get news records
        $query = "SELECT id FROM il_news_item" .
            " WHERE context_obj_id = " . $ilDB->quote($a_context_obj_id, "integer") .
            " AND context_obj_type = " . $ilDB->quote($a_context_obj_type, "text") .
            $and;

        $news_set = $ilDB->query($query);

        $news_arr = [];
        while ($news = $ilDB->fetchAssoc($news_set)) {
            $news_arr[] = new ilNewsItem((int) $news["id"]);
        }
        return $news_arr;
    }

    /**
     * Delete all news of a context
     * @deprecated will move to ilNewsData
     */
    public static function deleteNewsOfContext(
        int $a_context_obj_id,
        string $a_context_obj_type,
        int $a_context_sub_obj_id = 0,
        string $a_context_sub_obj_type = ""
    ): void {
        foreach (self::getNewsOfContext(
            $a_context_obj_id,
            $a_context_obj_type,
            $a_context_sub_obj_id,
            $a_context_sub_obj_type
        ) as $n) {
            $n->delete();
        }
    }

    /**
     * Lookup News Title
     * @deprecated will move to ilNewsData
     */
    public static function _lookupTitle(int $a_news_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT title FROM il_news_item WHERE id = " .
            $ilDB->quote($a_news_id, "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);
        return $rec["title"] ?? '';
    }

    /**
     * Lookup News Visibility
     * @deprecated will move to ilNewsData
     */
    public static function _lookupVisibility(int $a_news_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT visibility FROM il_news_item WHERE id = " .
            $ilDB->quote($a_news_id, "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        return $rec["visibility"] ?? NEWS_USERS;
    }

    /**
     * Lookup mob id
     * @deprecated will move to ilNewsData
     */
    public static function _lookupMobId(int $a_news_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT mob_id FROM il_news_item WHERE id = " .
            $ilDB->quote($a_news_id, "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);
        return (int) ($rec["mob_id"] ?? 0);
    }

    /**
     * Checks whether news are available for
     * @deprecated will move to ilNewsData
     */
    public static function filterObjIdsPerNews(
        array $a_obj_ids,
        int $a_time_period = 0,
        string $a_starting_date = "",
        string $a_ending_date = '',
        bool $ignore_period = false
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $and = "";
        if ($a_time_period > 0) {
            $limit_ts = self::handleTimePeriod($a_time_period);
            $and = " AND creation_date >= " . $ilDB->quote($limit_ts, "timestamp") . " ";
        }

        if ($a_starting_date !== "") {
            $and .= " AND creation_date >= " . $ilDB->quote($a_starting_date, "timestamp");
        }

        $query = "SELECT DISTINCT(context_obj_id) AS obj_id FROM il_news_item" .
            " WHERE " . $ilDB->in("context_obj_id", $a_obj_ids, false, "integer") . " " . $and;
        //" WHERE context_obj_id IN (".implode(ilUtil::quoteArray($a_obj_ids),",").")".$and;

        $set = $ilDB->query($query);
        $objs = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $objs[] = $rec["obj_id"];
        }

        return $objs;
    }

    /**
     * Determine title for news item entry
     */
    public static function determineNewsTitleByNewsId(
        int $a_news_id,
        int $a_agg_ref_id = 0,
        array $a_aggregation = []
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT context_obj_type, content_is_lang_var, title FROM il_news_item WHERE id = " .
            $ilDB->quote($a_news_id, "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        return self::determineNewsTitle(
            $rec["context_obj_type"],
            $rec["title"],
            $rec["content_is_lang_var"],
            $a_agg_ref_id,
            $a_aggregation
        );
    }

    /**
     * Determine title for news item entry
     * @deprecated will move to util?
     */
    public static function determineNewsTitle(
        string $a_context_obj_type,
        string $a_title,
        bool $a_content_is_lang_var,
        int $a_agg_ref_id = 0,
        array $a_aggregation = []
    ): string {
        global $DIC;

        $lng = $DIC->language();
        $obj_definition = $DIC["objDefinition"];
        $tit = "";

        if ($a_agg_ref_id > 0) {
            $cnt = count($a_aggregation);

            // forums
            if ($a_context_obj_type === "frm") {
                if ($cnt > 1) {
                    return sprintf($lng->txt("news_x_postings"), $cnt);
                }

                return $lng->txt("news_1_postings");
            }

            // files
            $up_cnt = $cr_cnt = 0;
            foreach ($a_aggregation as $item) {
                if ($item["title"] === "file_updated") {
                    $up_cnt++;
                } else {
                    $cr_cnt++;
                }
            }
            $sep = "";
            if ($cr_cnt === 1) {
                $tit = $lng->txt("news_1_file_created");
                $sep = "<br />";
            } elseif ($cr_cnt > 1) {
                $tit = sprintf($lng->txt("news_x_files_created"), $cr_cnt);
                $sep = "<br />";
            }
            if ($up_cnt === 1) {
                $tit .= $sep . $lng->txt("news_1_file_updated");
            } elseif ($up_cnt > 1) {
                $tit .= $sep . sprintf($lng->txt("news_x_files_updated"), $up_cnt);
            }
            return $tit;
        }

        if ($a_content_is_lang_var) {
            if ($obj_definition->isPlugin($a_context_obj_type)) {
                return ilObjectPlugin::lookupTxtById($a_context_obj_type, $a_title);
            }
            return $lng->txt($a_title);
        }

        return $a_title;
    }

    /**
     * Determine new content
     * @deprecated will move to util?
     */
    public static function determineNewsContent(
        string $a_context_obj_type,
        string $a_content,
        bool $a_is_lang_var
    ): string {
        global $DIC;

        $lng = $DIC->language();
        $obj_definition = $DIC["objDefinition"];

        if ($a_is_lang_var) {
            if ($obj_definition->isPlugin($a_context_obj_type)) {
                return ilObjectPlugin::lookupTxtById($a_context_obj_type, $a_content);
            }
            $lng->loadLanguageModule($a_context_obj_type);
            return $lng->txt($a_content);
        }

        return $a_content;
    }

    /**
     * Get first new id of news set related to a certain context
     * @deprecated will move to ilNewsData
     */
    public static function getFirstNewsIdForContext(
        int $a_context_obj_id,
        string $a_context_obj_type,
        int $a_context_sub_obj_id = 0,
        string $a_context_sub_obj_type = ""
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        // Determine how many rows should be deleted
        $query = "SELECT id " .
            "FROM il_news_item " .
            "WHERE " .
                "context_obj_id = " . $ilDB->quote($a_context_obj_id, "integer") .
                " AND context_obj_type = " . $ilDB->quote($a_context_obj_type, "text") .
                " AND context_sub_obj_id = " . $ilDB->quote($a_context_sub_obj_id, "integer") .
                " AND " . $ilDB->equals("context_sub_obj_type", $a_context_sub_obj_type, "text", true);

        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        return (int) ($rec["id"] ?? 0);
    }

    /**
     * Get last news id of news set related to a certain context
     * @deprecated will move to ilNewsData
     */
    public static function getLastNewsIdForContext(
        int $a_context_obj_id,
        string $a_context_obj_type,
        int $a_context_sub_obj_id = 0,
        string $a_context_sub_obj_type = "",
        bool $a_only_today = false
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        // Determine how many rows should be deleted
        $query = "SELECT id, update_date " .
            "FROM il_news_item " .
            "WHERE " .
                "context_obj_id = " . $ilDB->quote($a_context_obj_id, "integer") .
                " AND context_obj_type = " . $ilDB->quote($a_context_obj_type, "text") .
                " AND context_sub_obj_id = " . $ilDB->quote($a_context_sub_obj_id, "integer") .
                " AND " . $ilDB->equals("context_sub_obj_type", $a_context_sub_obj_type, "text", true) .
            " ORDER BY update_date DESC";

        $ilDB->setLimit(1, 0);
        $set = $ilDB->query($query);
        $id = 0;
        if ($rec = $ilDB->fetchAssoc($set)) {
            $id = (int) $rec["id"];
            if ($a_only_today) {
                $now = ilUtil::now();
                if (strpos($rec["update_date"], substr($now, 0, 10)) !== 0) {
                    $id = 0;
                }
            }
        }

        return $id;
    }


    /**
     * Lookup media object usage(s)
     * @deprecated will move to ilNewsData
     */
    public static function _lookupMediaObjectUsages(int $a_mob_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT id " .
            "FROM il_news_item " .
            "WHERE " .
                " mob_id = " . $ilDB->quote($a_mob_id, "integer");

        $usages = [];
        $set = $ilDB->query($query);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $usages[$rec["id"]] = ["type" => "news", "id" => $rec["id"]];
        }

        return $usages;
    }

    /**
     * Context Object ID
     * @deprecated will move to ilNewsData
     */
    public static function _lookupContextObjId(int $a_news_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT context_obj_id " .
            "FROM il_news_item " .
            "WHERE " .
                " id = " . $ilDB->quote($a_news_id, "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        return $rec["context_obj_id"];
    }

    /**
     * @deprecated will move to settings
     */
    public static function _lookupDefaultPDPeriod(): int
    {
        $news_set = new ilSetting("news");
        $per = $news_set->get("pd_period");
        if ((int) $per === 0) {
            $per = 30;
        }

        return $per;
    }

    /**
     * @deprecated will move to settings->user
     */
    public static function _lookupUserPDPeriod(int $a_user_id): int
    {
        $news_set = new ilSetting("news");
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
        $default_per = self::_lookupDefaultPDPeriod();

        $per = ilBlockSetting::_lookup(
            "pdnews",
            "news_pd_period",
            $a_user_id,
            0
        );

        // news period information
        if ($per <= 0 ||
            (!$allow_shorter_periods && ($per < $default_per)) ||
            (!$allow_longer_periods && ($per > $default_per))
            ) {
            $per = $default_per;
        }

        return (int) $per;
    }

    /**
     * @deprecated will move to settings
     */
    public static function _lookupRSSPeriod(): int
    {
        $news_set = new ilSetting("news");
        $rss_period = $news_set->get("rss_period");
        if ((int) $rss_period === 0) {		// default to two weeks
            $rss_period = 14;
        }
        return $rss_period;
    }

    /**
     * @deprecated will move to settings->user
     */
    public static function setPrivateFeedId(int $a_userId): void
    {
        self::$privFeedId = $a_userId;
    }

    /**
     * @deprecated will move to settings->user
     */
    public static function getPrivateFeedId(): int
    {
        return self::$privFeedId;
    }

    /**
     * Deliver mob file
     *
     */
    public function deliverMobFile(
        string $a_purpose = "Standard",
        bool $a_increase_download_cnt = false
    ): bool {
        $mob = $this->getMobId();
        $mob = new ilObjMediaObject($mob);
        $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());

        // check purpose
        if (!$mob->hasPurposeItem($a_purpose)) {
            return false;
        }

        $m_item = $mob->getMediaItem($a_purpose);
        if ($m_item->getLocationType() !== "Reference") {
            $file = $mob_dir . "/" . $m_item->getLocation();
            if (file_exists($file) && is_file($file)) {
                if ($a_increase_download_cnt) {
                    $this->increaseDownloadCounter();
                }
                ilFileDelivery::deliverFileLegacy($file, $m_item->getLocation(), "", false, false, false);
                return true;
            }

            $this->main_tpl->setOnScreenMessage('failure', "File not found!", true);
            return false;
        }

        if ($a_increase_download_cnt) {
            $this->increaseDownloadCounter();
        }

        ilUtil::redirect($m_item->getLocation());
    }

    /**
     * Increase download counter
     * @deprecated will move to data
     */
    public function increaseDownloadCounter(): void
    {
        $ilDB = $this->db;

        $cnt = $this->getMobDownloadCounter();
        $cnt++;
        $this->setMobDownloadCounter($cnt);
        $ilDB->manipulate(
            "UPDATE il_news_item SET " .
            " mob_cnt_download = " . $ilDB->quote($cnt, "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Increase play counter
     *
     * @deprecated will move to data
     */
    public function increasePlayCounter(): void
    {
        $ilDB = $this->db;

        $cnt = $this->getMobPlayCounter();
        $cnt++;
        $this->setMobPlayCounter($cnt);
        $ilDB->manipulate(
            "UPDATE il_news_item SET " .
            " mob_cnt_play = " . $ilDB->quote($cnt, "integer") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Prepare news data from cache
     * @deprecated will move to data
     */
    public static function prepareNewsDataFromCache(array $a_cres): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $data = $a_cres;
        $news_ids = array_keys($data);
        $set = $ilDB->query("SELECT id FROM il_news_item " .
            " WHERE " . $ilDB->in("id", $news_ids, false, "integer"));
        $existing_ids = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $existing_ids[] = (int) $rec["id"];
        }
        //var_dump($existing_ids);
        $existing_news = [];
        foreach ($data as $k => $v) {
            if (in_array($k, $existing_ids)) {
                $existing_news[$k] = $v;
            }
        }

        return $existing_news;
    }
}
