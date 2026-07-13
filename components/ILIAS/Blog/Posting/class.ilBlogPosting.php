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

declare(strict_types=1);

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\Posting\PostingManager;

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
    protected InternalDataService $internal_data;
    protected PostingManager $posting_manager;

    public function afterConstructor(): void
    {
        global $DIC;
        $this->internal_data = $DIC->blog()->internal()->data();
        $this->posting_manager = $DIC->blog()->internal()->domain()->posting();
    }

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
        $data = $this->internal_data;

        if (!$a_import) {
            $created = ilUtil::now();
        } else {
            $created = $this->getCreated()->get(IL_CAL_DATETIME);
        }

        $post = $data->posting(
            0,
            $this->getBlogId(),
            $this->getTitle(),
            new ilDateTime($created, IL_CAL_DATETIME),
            $this->getAuthor(),
            $this->isApproved(),
            $this->getWithdrawn()
        );
        $id = $this->posting_manager->create($post);
        $this->setId($id);

        if (!$a_import) {
            parent::create($a_import);
        }
    }

    public function update(
        bool $a_validate = true,
        bool $a_no_history = false,
        bool $a_notify = true,
        string $a_notify_action = "update"
    ): array|bool {
        $data = $this->internal_data;
        $post = $data->posting(
            $this->getId(),
            $this->getBlogId(),
            $this->getTitle(),
            $this->getCreated(),
            $this->getAuthor(),
            $this->isApproved(),
            $this->getWithdrawn()
        );
        $this->posting_manager->update($post);

        $ret = parent::update($a_validate, $a_no_history);

        if ($a_notify && $this->getActive()) {
            ilObjBlog::sendNotification(
                $a_notify_action,
                $this->blog_node_is_wsp,
                $this->blog_node_id,
                $this->getId()
            );
        }

        return $ret;
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

        $this->posting_manager->delete($this->getId());

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

        $lom_services = $DIC->learningObjectMetadata();
        $mgr = $DIC->blog()->internal()->domain()->posting();
        foreach ($mgr->getAllByBlog($a_blog_id, 0) as $posting) {
            $lom_services->deleteAll($a_blog_id, $posting->getId(), "blp");
            $post = new ilBlogPosting($posting->getId());
            $post->delete();
        }
    }

    public static function lookupBlogId(
        int $a_posting_id
    ): ?int {
        global $DIC;
        return $DIC->blog()->internal()->domain()->posting()->lookupBlogId($a_posting_id);
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

        $pages = parent::getAllPages("blp", $a_blog_id);
        $posts = [];
        foreach ($DIC->blog()->internal()->domain()->posting()->getAllByBlog(
            $a_blog_id,
            $a_limit,
            $a_offset
        ) as $posting) {
            $id = $posting->getId();
            if (isset($pages[$id])) {
                $posts[$id] = $pages[$id];
                $posts[$id]["title"] = $posting->getTitle();
                $posts[$id]["created"] = $posting->getCreated();
                $posts[$id]["author"] = $posting->getAuthor();
                $posts[$id]["approved"] = $posting->isApproved();
                $posts[$id]["last_withdrawn"] = $posting->getLastWithdrawn();

                foreach (self::getPageContributors("blp", $id) as $editor) {
                    if ($editor["user_id"] != $posting->getAuthor()) {
                        $posts[$id]["editors"][] = $editor["user_id"];
                    }
                }
            }
        }

        return $posts;
    }

    /**
     * Checks whether a posting exists
     */
    public static function exists(
        int $a_blog_id,
        int $a_posting_id
    ): bool {
        global $DIC;
        return $DIC->blog()->internal()->domain()->posting()->exists(
            $a_blog_id,
            $a_posting_id
        );
    }

    /**
     * Get newest posting for blog
     */
    public static function getLastPost(
        int $a_blog_id
    ): int {
        global $DIC;
        return $DIC->blog()->internal()->domain()->posting()->getLastPost($a_blog_id);
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

    public function getNotificationAbstract(): string
    {
        $snippet = ilBlogPostingGUI::getSnippet($this->getId(), true);

        // making things more readable
        $snippet = str_replace(array('<br/>', '<br />', '</p>', '</div>'), "\n", $snippet);

        return trim(strip_tags($snippet));
    }

    // keywords
    public function updateKeywords(
        array $keywords
    ): void {
        $this->lom_services->manipulate($this->getBlogId(), $this->getId(), "blp")
                           ->prepareDelete($this->lom_services->paths()->keywords())
                           ->prepareCreateOrUpdate($this->lom_services->paths()->keywords(), ...$keywords)
                           ->execute();
    }

    public static function getKeywords(
        int $a_obj_id,
        int $a_posting_id
    ): array {
        global $DIC;

        $lom_services = $DIC->learningObjectMetadata();

        $result = [];
        $keywords = $lom_services->read($a_obj_id, $a_posting_id, "blp")
                                 ->allData($lom_services->paths()->keywords());
        foreach ($keywords as $keyword) {
            if ($keyword->value() !== "") {
                $result[] = $keyword->value();
            }
        }

        return $result;
    }
}
