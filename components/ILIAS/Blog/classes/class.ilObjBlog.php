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

use ILIAS\Blog\Settings\SettingsManager;
use ILIAS\Blog\Settings\Settings;

class ilObjBlog extends ilObject2
{
    public const NAV_MODE_LIST = 1;
    public const NAV_MODE_MONTH = 2;
    public const ABSTRACT_DEFAULT_SHORTEN_LENGTH = 500;
    public const ABSTRACT_DEFAULT_IMAGE_WIDTH = 144;
    public const ABSTRACT_DEFAULT_IMAGE_HEIGHT = 144;
    public const NAV_MODE_LIST_DEFAULT_POSTINGS = 10;
    protected ?Settings $blog_settings = null;
    protected SettingsManager $settings_manager;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;
    protected \ILIAS\Notes\Service $notes_service;

    protected bool $notes = false;
    protected bool $style = false;

    public function __construct(
        int $a_id = 0,
        bool $a_reference = true
    ) {
        global $DIC;

        $this->notes_service = $DIC->notes();
        parent::__construct($a_id, $a_reference);
        $this->rbac_review = $DIC->rbac()->review();

        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain();
        $this->settings_manager = $DIC->blog()->internal()->domain()->blogSettings();
        if ($this->getId() > 0) {
            $this->blog_settings = $this->settings_manager->getByObjId($this->getId());
        }
    }

    protected function initType(): void
    {
        $this->type = "blog";
    }

    protected function doRead(): void
    {
        // #14661
        $this->setNotesStatus(
            $this->notes_service->domain()->commentsActive($this->id)
        );
        $this->blog_settings = $this->settings_manager->getByObjId($this->getId());
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $ilDB = $this->db;

        $this->createMetaData();

        $ilDB->manipulate("INSERT INTO il_blog (id,ppic,rss_active,approval" .
            ",abs_shorten,abs_shorten_len,abs_image,abs_img_width,abs_img_height" .
            ",keywords,authors,nav_mode,nav_list_mon_with_post,ov_post) VALUES (" .
            $ilDB->quote($this->id, "integer") . "," .
            $ilDB->quote(true, "integer") . "," .
            $ilDB->quote(true, "integer") . "," .
            $ilDB->quote(false, "integer") . "," .
            $ilDB->quote(false, "integer") . "," .
            $ilDB->quote(0, "integer") . "," .
            $ilDB->quote(false, "integer") . "," .
            $ilDB->quote(0, "integer") . "," .
            $ilDB->quote(0, "integer") . "," .
            $ilDB->quote(true, "integer") . "," .
            $ilDB->quote(false, "integer") . "," .
            $ilDB->quote(self::NAV_MODE_LIST, "integer") . "," .
            $ilDB->quote(5, "integer") . "," .
            $ilDB->quote(5, "integer") .
            ")");

        // #14661
        $this->notes_service->domain()->activateComments($this->id);
    }

    protected function doDelete(): void
    {
        $ilDB = $this->db;

        $this->deleteMetaData();

        ilBlogPosting::deleteAllBlogPostings($this->id);

        // remove all notifications
        ilNotification::removeForObject(ilNotification::TYPE_BLOG, $this->id);

        $ilDB->manipulate("DELETE FROM il_blog" .
            " WHERE id = " . $ilDB->quote($this->id, "integer"));
    }

    protected function doUpdate(): void
    {
        $this->updateMetaData();

        if ($this->id) {
            // #14661
            $this->notes_service->domain()->activateComments(
                $this->id,
                $this->getNotesStatus()
            );
        }
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        assert($new_obj instanceof ilObjBlog);

        $new_obj->setNotesStatus($this->getNotesStatus());
        $new_obj->update();

        $this->settings_manager->clone($this->getId(), $new_obj->getId());

        // set/copy stylesheet
        $this->content_style_domain->styleForObjId($this->getId())->cloneTo($new_obj->getId());

        $this->cloneMetaData($new_obj);
    }

    public function getNotesStatus(): bool
    {
        return $this->notes;
    }

    public function setNotesStatus(bool $a_status): void
    {
        $this->notes = $a_status;
    }

    public static function sendNotification(
        string $a_action,
        bool $a_in_wsp,
        int $a_blog_node_id,
        int $a_posting_id,
        ?string $a_comment = null
    ): void {
        global $DIC;

        $ilUser = $DIC->user();

        // get blog object id (repository or workspace)
        if ($a_in_wsp) {
            $tree = new ilWorkspaceTree($ilUser->getId()); // owner of tree is irrelevant
            $blog_obj_id = $tree->lookupObjectId($a_blog_node_id);
            $access_handler = new ilWorkspaceAccessHandler($tree);
        } else {
            $blog_obj_id = ilObject::_lookupObjId($a_blog_node_id);
            $access_handler = null;
        }
        if (!$blog_obj_id) {
            return;
        }

        $posting = new ilBlogPosting($a_posting_id);

        // #11138
        $ignore_threshold = ($a_action === "comment");

        $admin_only = false;

        // approval handling
        if (!$posting->isApproved()) {
            $blog_settings = $DIC->blog()->internal()->domain()->blogSettings()->getByObjId($blog_obj_id);
            if ($blog_settings?->getApproval()) {
                switch ($a_action) {
                    case "update":
                        // un-approved posting was updated - no notifications
                        return;

                    case "new":
                        // un-approved posting was activated - admin-only notification
                        $admin_only = true;
                        $ignore_threshold = true;
                        $a_action = "approve";
                        break;
                }
            }
        }

        // create/update news item (only in repository)
        if (!$a_in_wsp &&
            in_array($a_action, array("update", "new"))) {
            $posting->handleNews(($a_action === "update"));
        }

        // recipients
        $users = ilNotification::getNotificationsForObject(
            ilNotification::TYPE_BLOG,
            $blog_obj_id,
            $a_posting_id,
            $ignore_threshold
        );
        if (!count($users)) {
            return;
        }

        $ntf = new ilSystemNotification($a_in_wsp);
        $ntf->setLangModules(array("blog"));
        $ntf->setRefId($a_blog_node_id);
        $ntf->setChangedByUserId($ilUser->getId());
        $ntf->setSubjectLangId('blog_change_notification_subject');
        $ntf->setIntroductionLangId('blog_change_notification_body_' . $a_action);
        $ntf->addAdditionalInfo('blog_posting', $posting->getTitle());
        if ($a_comment) {
            $ntf->addAdditionalInfo('comment', $a_comment, true);
        }
        $ntf->setGotoLangId('blog_change_notification_link');
        $ntf->setReasonLangId('blog_change_notification_reason');

        $abstract = $posting->getNotificationAbstract();
        if ($abstract) {
            $ntf->addAdditionalInfo('content', $abstract, true);
        }

        $notified = $ntf->sendMailAndReturnRecipients(
            $users,
            "_" . $a_posting_id,
            ($admin_only ? "write" : "read")
        );

        // #14387
        if (count($notified)) {
            ilNotification::updateNotificationTime(ilNotification::TYPE_BLOG, $blog_obj_id, $notified, $a_posting_id);
        }
    }

    /**
     * Deliver blog as rss feed
     */
    public static function deliverRSS(string $a_wsp_id): void
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        if (!$ilSetting->get('enable_global_profiles')) {
            return;
        }

        // #10827
        if (!str_ends_with($a_wsp_id, "_cll")) {
            $wsp_id = new ilWorkspaceTree(0);
            $obj_id = $wsp_id->lookupObjectId((int) $a_wsp_id);
            $is_wsp = "_wsp";
            $pl = $DIC->blog()->internal()->gui()->permanentLink(0, (int) $a_wsp_id);
        } else {
            $a_wsp_id = substr($a_wsp_id, 0, -4);
            $obj_id = ilObject::_lookupObjId((int) $a_wsp_id);
            $is_wsp = null;
            $pl = $DIC->blog()->internal()->gui()->permanentLink((int) $a_wsp_id);
        }
        if (!$obj_id) {
            return;
        }

        $blog_settings = $DIC->blog()->internal()->domain()->blogSettings()
            ->getByObjId($obj_id);
        if (!$blog_settings?->getRSS()) {
            return;
        }

        $blog = new self($obj_id, false);
        $feed = new ilFeedWriter();

        $url = $pl->getPermanentLink();
        $url = str_replace("&", "&amp;", $url);

        // #11870
        $feed->setChannelTitle(str_replace("&", "&amp;", $blog->getTitle()));
        $feed->setChannelDescription(str_replace("&", "&amp;", $blog->getDescription()));
        $feed->setChannelLink($url);

        // needed for blogpostinggui / pagegui
        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);

        foreach (ilBlogPosting::getAllPostings($obj_id) as $item) {
            $id = $item["id"];

            // only published items
            $is_active = ilBlogPosting::_lookupActive($id, "blp");
            if (!$is_active) {
                continue;
            }

            // #16434
            $snippet = strip_tags(ilBlogPostingGUI::getSnippet($id), "<br><br/><div><p>");
            $snippet = str_replace("&", "&amp;", $snippet);
            $snippet = "<![CDATA[" . $snippet . "]]>";

            $url = $pl->getPermanentLink((int) $id);
            $url = str_replace("&", "&amp;", $url);

            $feed_item = new ilFeedItem();
            $feed_item->setTitle(str_replace("&", "&amp;", $item["title"])); // #16022
            $feed_item->setDate($item["created"]->get(IL_CAL_DATETIME));
            $feed_item->setDescription($snippet);
            $feed_item->setLink($url);
            $feed_item->setAbout($url);
            $feed->addItem($feed_item);
        }

        $feed->showFeed();
        exit();
    }

    public function initDefaultRoles(): void
    {
        ilObjRole::createDefaultRole(
            'il_blog_contributor_' . $this->getRefId(),
            "Contributor of blog obj_no." . $this->getId(),
            'il_blog_contributor',
            $this->getRefId()
        );

        ilObjRole::createDefaultRole(
            'il_blog_editor_' . $this->getRefId(),
            "Editor of blog obj_no." . $this->getId(),
            'il_blog_editor',
            $this->getRefId()
        );
    }

    public function getLocalContributorRole(int $a_node_id): int
    {
        foreach ($this->rbac_review->getLocalRoles($a_node_id) as $role_id) {
            if (str_starts_with(ilObject::_lookupTitle($role_id), "il_blog_contributor")) {
                return $role_id;
            }
        }
        return 0;
    }

    public function getLocalEditorRole(int $a_node_id): int
    {
        foreach ($this->rbac_review->getLocalRoles($a_node_id) as $role_id) {
            if (strpos(ilObject::_lookupTitle($role_id), "il_blog_editor") === 0) {
                return $role_id;
            }
        }
        return 0;
    }

    public function getAllLocalRoles(int $a_node_id): array
    {
        $res = array();
        foreach ($this->rbac_review->getLocalRoles($a_node_id) as $role_id) {
            $res[$role_id] = ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id));
        }

        asort($res);
        return $res;
    }

    public function getRolesWithContributeOrRedact(int $a_node_id): array
    {
        $contr_op_id = ilRbacReview::_getOperationIdByName("contribute");
        $redact_op_id = ilRbacReview::_getOperationIdByName("redact");
        $contr_role_id = $this->getLocalContributorRole($a_node_id);
        $editor_role_id = $this->getLocalEditorRole($a_node_id);

        $res = array();
        foreach ($this->rbac_review->getParentRoleIds($a_node_id) as $role_id => $role) {
            if ($role_id != $contr_role_id &&
                $role_id != $editor_role_id) {
                $all_ops = $this->rbac_review->getActiveOperationsOfRole($a_node_id, $role_id);
                if (in_array($contr_op_id, $all_ops) ||
                    in_array($redact_op_id, $all_ops)) {
                    $res[$role_id] = ilObjRole::_getTranslation($role["title"]);
                }
            }
        }

        return $res;
    }
}
