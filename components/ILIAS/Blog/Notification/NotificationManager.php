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

namespace ILIAS\Blog\Notification;

use ILIAS\Blog\InternalDomainService;

class NotificationManager
{
    protected int $current_user_id;

    public function __construct(
        protected InternalDomainService $domain
    ) {
        $this->current_user_id = $this->domain->user()->getId();
    }

    public function sendNotification(
        string $action,
        bool $in_wsp,
        int $blog_node_id,
        int $posting_id,
        ?string $comment = null
    ): void {
        $blog_obj_id = $this->getBlogObjectId($in_wsp, $blog_node_id);
        if (!$blog_obj_id) {
            return;
        }

        $posting = new \ilBlogPosting($posting_id);

        $ignore_threshold = ($action === "comment");
        $admin_only = false;

        if (!$posting->isApproved()) {
            $blog_settings = $this->domain->blogSettings()->getByObjId($blog_obj_id);
            if ($blog_settings?->getApproval()) {
                switch ($action) {
                    case "update":
                        return;

                    case "new":
                        $admin_only = true;
                        $ignore_threshold = true;
                        $action = "approve";
                        break;
                }
            }
        }

        if (!$in_wsp && in_array($action, array("update", "new"))) {
            $this->domain->news()->handle($posting, ($action === "update"));
        }

        $users = \ilNotification::getNotificationsForObject(
            \ilNotification::TYPE_BLOG,
            $blog_obj_id,
            $posting_id,
            $ignore_threshold
        );
        if (!count($users)) {
            return;
        }

        $this->sendSystemNotification(
            $in_wsp,
            $blog_node_id,
            $action,
            $posting,
            $comment,
            $users,
            $admin_only
        );

        if (count($users)) {
            \ilNotification::updateNotificationTime(
                \ilNotification::TYPE_BLOG,
                $blog_obj_id,
                $users,
                $posting_id
            );
        }
    }

    protected function getBlogObjectId(bool $in_wsp, int $blog_node_id): ?int
    {
        if ($in_wsp) {
            $tree = new \ilWorkspaceTree($this->current_user_id);
            return $tree->lookupObjectId($blog_node_id);
        } else {
            return \ilObject::_lookupObjId($blog_node_id);
        }
    }

    protected function sendSystemNotification(
        bool $in_wsp,
        int $blog_node_id,
        string $action,
        \ilBlogPosting $posting,
        ?string $comment,
        array $users,
        bool $admin_only
    ): void {
        $ntf = new \ilSystemNotification($in_wsp);
        $ntf->setLangModules(array("blog"));
        $ntf->setRefId($blog_node_id);
        $ntf->setChangedByUserId($this->current_user_id);
        $ntf->setSubjectLangId('blog_change_notification_subject');
        $ntf->setIntroductionLangId('blog_change_notification_body_' . $action);
        $ntf->addAdditionalInfo('blog_posting', $posting->getTitle());

        if ($comment) {
            $ntf->addAdditionalInfo('comment', $comment, true);
        }

        $ntf->setGotoLangId('blog_change_notification_link');
        $ntf->setReasonLangId('blog_change_notification_reason');

        $abstract = $posting->getNotificationAbstract();
        if ($abstract) {
            $ntf->addAdditionalInfo('content', $abstract, true);
        }

        $notified = $ntf->sendMailAndReturnRecipients(
            $users,
            (string) $posting->getId(),
            ($admin_only ? "write" : "read")
        );

        if (count($notified)) {
            \ilNotification::updateNotificationTime(
                \ilNotification::TYPE_BLOG,
                $posting->getBlogId(),
                $notified,
                $posting->getId()
            );
        }
    }
}
