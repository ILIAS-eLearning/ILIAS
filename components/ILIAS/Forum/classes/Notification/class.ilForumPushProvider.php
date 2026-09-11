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

use ILIAS\Notifications\Interfaces\PushProviderInterface;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationParameter;
use ILIAS\Notifications\Provider\NotificationsPushProvider;

final class ilForumPushProvider implements PushProviderInterface
{
    public const string IDENTIFIER = 'forum';

    private ?NotificationsPushProvider $push_provider = null;

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function getName(ilLanguage $lng): string
    {
        $lng->loadLanguageModule('forum');

        return $lng->txt('forum');
    }

    public function getDescription(ilLanguage $lng): string
    {
        $lng->loadLanguageModule('forum');

        return $lng->txt('forums_forum_push_notification_desc');
    }

    /**
     * @param int[] $recipients
     * @return int[]
     */
    public function filterMailRecipients(
        array $recipients,
        ilForumNotificationMailData $provider,
        int $notification_type,
        ilLogger $logger
    ): array {
        global $DIC;

        if (!$DIC->settings()->get('forum_notification', '0')) {
            return [];
        }

        $mail_recipients = [];

        foreach ($recipients as $recipient_id) {
            if ($this->sendPush($recipient_id, $provider, $notification_type)) {
                $logger->debug(sprintf('Push notification sent to user "%s".', $recipient_id));
                continue;
            }

            $mail_recipients[] = $recipient_id;
        }

        return $mail_recipients;
    }

    private function sendPush(
        int $recipient_id,
        ilForumNotificationMailData $provider,
        int $notification_type
    ): bool {
        $user = ilObjectFactory::getInstanceByObjId($recipient_id, false);
        if (!$user instanceof ilObjUser) {
            return false;
        }

        $lng = ilLanguageFactory::_getLanguageOfUser($recipient_id);
        $lng->loadLanguageModule('forum');

        $content = $this->buildPushContent($provider, $notification_type, $lng);
        if ($content === null) {
            return false;
        }

        return $this->getPushProvider()->push(
            $user,
            $content['title'],
            $content['description'],
            $this->buildPushLink($provider)
        );
    }

    /**
     * @return array{title: string, description: string}|null
     */
    private function buildPushContent(
        ilForumNotificationMailData $provider,
        int $notification_type,
        ilLanguage $lng
    ): ?array {
        $subject_key = $this->getSubjectLanguageKey($notification_type);
        if ($subject_key === null) {
            return null;
        }

        $container_text = '';
        if ($provider->providesClosestContainer()) {
            $container_text = ' (' .
                $lng->txt('frm_noti_obj_' . $provider->closestContainer()->getType()) .
                ' "' . $provider->closestContainer()->getTitle() . '")';
        }

        $title = sprintf(
            $lng->txt($subject_key),
            $provider->getForumTitle(),
            $container_text,
            $provider->getThreadTitle()
        );

        $description = match ($notification_type) {
            ilForumMailNotification::TYPE_THREAD_DELETED => sprintf(
                $lng->txt('thread_deleted_by'),
                $provider->getDeletedBy(),
                $provider->getForumTitle()
            ),
            ilForumMailNotification::TYPE_POST_NEW => sprintf(
                $lng->txt('frm_noti_new_post'),
                $provider->getForumTitle()
            ),
            ilForumMailNotification::TYPE_POST_ACTIVATION => $lng->txt('forums_post_activation_mail'),
            ilForumMailNotification::TYPE_POST_ANSWERED => $lng->txt('forum_post_replied'),
            ilForumMailNotification::TYPE_POST_UPDATED => sprintf(
                $lng->txt('post_updated_by'),
                $provider->getPostUpdateUserName($lng),
                $provider->getForumTitle()
            ),
            ilForumMailNotification::TYPE_POST_CENSORED => sprintf(
                $lng->txt('post_censored_by'),
                $provider->getPostUpdateUserName($lng),
                $provider->getForumTitle()
            ),
            ilForumMailNotification::TYPE_POST_UNCENSORED => sprintf(
                $lng->txt('post_uncensored_by'),
                $provider->getPostUpdateUserName($lng)
            ),
            ilForumMailNotification::TYPE_POST_DELETED => sprintf(
                $lng->txt('post_deleted_by'),
                $provider->getDeletedBy(),
                $provider->getForumTitle()
            ),
            default => null,
        };

        if ($description === null) {
            return null;
        }

        return [
            'title' => $title,
            'description' => $description,
        ];
    }

    private function getSubjectLanguageKey(int $notification_type): ?string
    {
        return match ($notification_type) {
            ilForumMailNotification::TYPE_THREAD_DELETED => 'frm_noti_subject_del_thread',
            ilForumMailNotification::TYPE_POST_NEW => 'frm_noti_subject_new_post',
            ilForumMailNotification::TYPE_POST_ACTIVATION => 'frm_noti_subject_act_post',
            ilForumMailNotification::TYPE_POST_ANSWERED => 'frm_noti_subject_answ_post',
            ilForumMailNotification::TYPE_POST_UPDATED => 'frm_noti_subject_upt_post',
            ilForumMailNotification::TYPE_POST_CENSORED => 'frm_noti_subject_cens_post',
            ilForumMailNotification::TYPE_POST_UNCENSORED => 'frm_noti_subject_uncens_post',
            ilForumMailNotification::TYPE_POST_DELETED => 'frm_noti_subject_del_post',
            default => null,
        };
    }

    private function buildPushLink(ilForumNotificationMailData $provider): ilNotificationLink
    {
        $url = rtrim(ilUtil::_getHttpPath(), '/') . '/goto.php?target=frm_' . implode('_', [
            $provider->getRefId(),
            $provider->getThreadId(),
            $provider->getPostId(),
        ]) . '&client_id=' . CLIENT_ID;

        return new ilNotificationLink(
            new ilNotificationParameter('forums_notification_show_post', [], 'forum'),
            $url
        );
    }

    private function getPushProvider(): NotificationsPushProvider
    {
        return $this->push_provider ??= new NotificationsPushProvider($this);
    }
}
