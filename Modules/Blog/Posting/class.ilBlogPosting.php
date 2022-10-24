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
 * Class ilBlogPosting
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBlogPosting extends ilPageObject
{
    protected string $title = "";
    protected ?ilDateTime $created = null;
    protected int $blog_node_id = 0;
    protected bool $blog_node_is_wsp = false;
    protected int $author = 0;
    protected bool $approved = false;
    protected ?ilDateTime $withdrawn = null;

    public function getParentType(): string
    {
        return "blp";
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setBlogId(int $a_id): void
    {
        $this->setParentId($a_id);
    }

    public function getBlogId(): int
    {
        return $this->getParentId();
    }

    public function setCreated(ilDateTime $a_date): void
    {
        $this->created = $a_date;
    }

    public function getCreated(): ilDateTime
    {
        return $this->created;
    }

    public function setAuthor(int $a_id): void
    {
        $this->author = $a_id;
    }

    public function getAuthor(): int
    {
        return $this->author;
    }

    public function setApproved(bool $a_status): void
    {
        $this->approved = $a_status;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    /**
     * Set last withdrawal date
     */
    public function setWithdrawn(
        ilDateTime $a_date
    ): void {
        $this->withdrawn = $a_date;
    }

    /**
     * Get last withdrawal date
     */
    public function getWithdrawn(): ?ilDateTime
    {
        return $this->withdrawn;
    }

    /**
     * Create new blog posting
     */
    public function create(
        bool $a_import = false
    ): void {
        $ilDB = $this->db;

        $id = $ilDB->nextId("il_blog_posting");
        $this->setId($id);

        if (!$a_import) {
            $created = ilUtil::now();
        } else {
            $created = $this->getCreated()->get(IL_CAL_DATETIME);
        }

        // we are using a separate creation date to enable sorting without JOINs
        $withdrawn = $this->getWithdrawn()
            ? $this->getWithdrawn()->get(IL_CAL_DATETIME)
            : null;
        $query = "INSERT INTO il_blog_posting (id, title, blog_id, created, author, approved, last_withdrawn)" .
            " VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getBlogId(), "integer") . "," .
            $ilDB->quote($created, "timestamp") . "," .
            $ilDB->quote($this->getAuthor(), "integer") . "," .
            $ilDB->quote($this->isApproved(), "integer") . "," . // #16526 - import
            $ilDB->quote($withdrawn, "timestamp") . ")";
        $ilDB->manipulate($query);

        if (!$a_import) {
            parent::create($a_import);
            // $this->saveInternalLinks($this->getXMLContent());
        }
    }

    public function update(
        bool $a_validate = true,
        bool $a_no_history = false,
        bool $a_notify = true,
        string $a_notify_action = "update"
    ): bool {
        $ilDB = $this->db;

        // blog_id, author and created cannot be changed
        $withdrawn = $this->getWithdrawn()
            ? $this->getWithdrawn()->get(IL_CAL_DATETIME)
            : null;
        $query = "UPDATE il_blog_posting SET" .
            " title = " . $ilDB->quote($this->getTitle(), "text") .
            ",created = " . $ilDB->quote($this->getCreated()->get(IL_CAL_DATETIME), "timestamp") .
            ",approved =" . $ilDB->quote($this->isApproved(), "integer") .
            ",last_withdrawn =" . $ilDB->quote($withdrawn, "timestamp") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        parent::update($a_validate, $a_no_history);

        if ($a_notify && $this->getActive()) {
            ilObjBlog::sendNotification($a_notify_action, $this->blog_node_is_wsp, $this->blog_node_id, $this->getId());
        }

        return true;
    }

    /**
     * Read blog posting
     */
    public function read(): void
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM il_blog_posting" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setTitle($rec["title"]);
        $this->setBlogId($rec["blog_id"]);
        $this->setCreated(new ilDateTime($rec["created"], IL_CAL_DATETIME));
        $this->setAuthor($rec["author"]);
        if ($rec["approved"]) {
            $this->setApproved(true);
        }
        $this->setWithdrawn(new ilDateTime($rec["last_withdrawn"], IL_CAL_DATETIME));

        // when posting is deactivated it should loose the approval
        $this->addUpdateListener($this, "checkApproval");

        parent::read();
    }

    public function checkApproval(): void
    {
        if (!$this->getActive() && $this->isApproved()) {
            $this->approved = false;
            $this->update();
        }
    }

    /**
     * Delete blog posting and all related data
     */
    public function delete(): void
    {
        $ilDB = $this->db;

        ilNewsItem::deleteNewsOfContext(
            $this->getBlogId(),
            "blog",
            $this->getId(),
            $this->getParentType()
        );

        $query = "DELETE FROM il_blog_posting" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        parent::delete();
    }

    /**
     * Unpublish
     */
    public function unpublish(): void
    {
        $this->setApproved(false);
        $this->setActive(false);
        $this->setWithdrawn(new ilDateTime(ilUtil::now(), IL_CAL_DATETIME));
        $this->update(true, false, false);

        ilNewsItem::deleteNewsOfContext(
            $this->getBlogId(),
            "blog",
            $this->getId(),
            $this->getParentType()
        );
    }


    /**
     * Delete all postings for blog
     */
    public static function deleteAllBlogPostings(
        int $a_blog_id
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM il_blog_posting" .
            " WHERE blog_id = " . $ilDB->quote($a_blog_id, "integer");
        $set = $ilDB->query($query);
        while ($rec = $ilDB->fetchAssoc($set)) {
            // delete all md keywords
            $md_obj = new ilMD($a_blog_id, $rec["id"], "blp");
            if (is_object($md_section = $md_obj->getGeneral())) {
                foreach ($md_section->getKeywordIds() as $id) {
                    $md_key = $md_section->getKeyword($id);
                    $md_key->delete();
                }
            }

            $post = new ilBlogPosting($rec["id"]);
            $post->delete();
        }
    }

    public static function lookupBlogId(
        int $a_posting_id
    ): ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT blog_id FROM il_blog_posting" .
            " WHERE id = " . $ilDB->quote($a_posting_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["blog_id"];
        }
        return null;
    }

    /**
     * Get all postings of blog
     */
    public static function getAllPostings(
        int $a_blog_id,
        int $a_limit = 1000,
        int $a_offset = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $pages = parent::getAllPages("blp", $a_blog_id);

        if ($a_limit) {
            $ilDB->setLimit($a_limit, $a_offset);
        }

        $query = "SELECT * FROM il_blog_posting" .
            " WHERE blog_id = " . $ilDB->quote($a_blog_id, "integer") .
            " ORDER BY created DESC";
        $set = $ilDB->query($query);
        $post = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (isset($pages[$rec["id"]])) {
                $post[$rec["id"]] = $pages[$rec["id"]];
                $post[$rec["id"]]["title"] = $rec["title"];
                $post[$rec["id"]]["created"] = new ilDateTime($rec["created"], IL_CAL_DATETIME);
                $post[$rec["id"]]["author"] = $rec["author"];
                $post[$rec["id"]]["approved"] = (bool) $rec["approved"];
                $post[$rec["id"]]["last_withdrawn"] = new ilDateTime($rec["last_withdrawn"], IL_CAL_DATETIME);

                foreach (self::getPageContributors("blp", $rec["id"]) as $editor) {
                    if ($editor["user_id"] != $rec["author"]) {
                        $post[$rec["id"]]["editors"][] = $editor["user_id"];
                    }
                }
            }
        }

        return $post;
    }

    /**
     * Checks whether a posting exists
     */
    public static function exists(
        int $a_blog_id,
        int $a_posting_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT id FROM il_blog_posting" .
            " WHERE blog_id = " . $ilDB->quote($a_blog_id, "integer") .
            " AND id = " . $ilDB->quote($a_posting_id, "integer");
        $set = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Get newest posting for blog
     */
    public static function getLastPost(
        int $a_blog_id
    ): int {
        $data = self::getAllPostings($a_blog_id, 1);
        if ($data) {
            $keys = array_keys($data);
            return end($keys);
        }
        return 0;
    }

    /**
     * Set blog node id (needed for notification)
     */
    public function setBlogNodeId(
        int $a_id,
        bool $a_is_in_workspace = false
    ): void {
        $this->blog_node_id = $a_id;
        $this->blog_node_is_wsp = $a_is_in_workspace;
    }

    /**
     * Get all blogs where user has postings
     */
    public static function searchBlogsByAuthor(
        int $a_user_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $ids = array();

        $sql = "SELECT DISTINCT(blog_id)" .
            " FROM il_blog_posting" .
            " WHERE author = " . $ilDB->quote($a_user_id);
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $ids[] = (int) $row["blog_id"];
        }
        return $ids;
    }

    public function getNotificationAbstract(): string
    {
        $snippet = ilBlogPostingGUI::getSnippet($this->getId(), true);

        // making things more readable
        $snippet = str_replace(array('<br/>', '<br />', '</p>', '</div>'), "\n", $snippet);

        return trim(strip_tags($snippet));
    }


    // keywords

    public function getMDSection(): ?ilMDGeneral
    {
        // general section available?
        $md_obj = new ilMD($this->getBlogId(), $this->getId(), "blp");
        if (!is_object($md_section = $md_obj->getGeneral())) {
            $md_section = $md_obj->addGeneral();
            $md_section->save();
            return $md_section;
        }
        return $md_section;
    }

    public function updateKeywords(
        array $keywords
    ): void {
        $ilUser = $this->user;

        // language is not "used" anywhere
        $ulang = $ilUser->getLanguage();
        $keywords = array($ulang => $keywords);

        ilMDKeyword::updateKeywords($this->getMDSection(), $keywords);
    }

    public static function getKeywords(
        int $a_obj_id,
        int $a_posting_id
    ): array {
        return ilMDKeyword::lookupKeywords($a_obj_id, $a_posting_id);
    }

    /**
     * Handle news item
     */
    public function handleNews(
        bool $a_update = false
    ): void {
        $lng = $this->lng;
        $ilUser = $this->user;

        // see ilWikiPage::updateNews()

        if (!$this->getActive()) {
            return;
        }

        $news_item = null;

        // try to re-use existing news item
        if ($a_update) {
            // get last news item of the day (if existing)
            $news_id = ilNewsItem::getLastNewsIdForContext(
                $this->getBlogId(),
                "blog",
                $this->getId(),
                $this->getParentType(),
                true
            );
            if ($news_id > 0) {
                $news_item = new ilNewsItem($news_id);
            }
        }

        // create new news item
        if (!$news_item) {
            $news_set = new ilSetting("news");
            $default_visibility = $news_set->get("default_visibility", "users");

            $news_item = new ilNewsItem();
            $news_item->setContext(
                $this->getBlogId(),
                "blog",
                $this->getId(),
                $this->getParentType()
            );
            $news_item->setPriority(NEWS_NOTICE);
            $news_item->setVisibility($default_visibility);
        }

        // news author
        $news_item->setUserId($ilUser->getId());


        // news title/content

        $news_item->setTitle($this->getTitle());

        $content = $a_update
            ? "blog_news_posting_updated"
            : "blog_news_posting_published";

        // news "author"
        $content = sprintf($lng->txt($content), ilUserUtil::getNamePresentation($ilUser->getId()));

        // posting author[s]
        $contributors = array();
        foreach (self::getPageContributors($this->getParentType(), $this->getId()) as $user) {
            $contributors[] = $user["user_id"];
        }
        if (count($contributors) > 1 || !in_array($this->getAuthor(), $contributors)) {
            // original author should come first?
            $authors = array(ilUserUtil::getNamePresentation($this->getAuthor()));
            foreach ($contributors as $user_id) {
                if ($user_id != $this->getAuthor()) {
                    $authors[] = ilUserUtil::getNamePresentation($user_id);
                }
            }
            $content .= "\n" . sprintf($lng->txt("blog_news_posting_authors"), implode(", ", $authors));
        }

        $news_item->setContentTextIsLangVar(false);
        $news_item->setContent($content);

        $snippet = ilBlogPostingGUI::getSnippet($this->getId());
        $news_item->setContentLong($snippet);

        if (!$news_item->getId()) {
            $news_item->create();
        } else {
            $news_item->update(true);
        }
    }

    /**
     * Lookup posting property
     */
    protected static function lookup(
        string $a_field,
        string $a_posting_id
    ): ?string {
        global $DIC;

        $db = $DIC->database();

        $set = $db->query("SELECT $a_field FROM il_blog_posting " .
            " WHERE id = " . $db->quote($a_posting_id, "integer"));
        $rec = $db->fetchAssoc($set);

        return $rec[$a_field] ?? null;
    }

    public static function lookupTitle(int $a_posting_id): string
    {
        return (string) self::lookup("title", $a_posting_id);
    }
}
