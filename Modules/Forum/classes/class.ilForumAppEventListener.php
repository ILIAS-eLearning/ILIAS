<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Forum listener. Listens to events of other components.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesForum
*/
class ilForumAppEventListener implements ilAppEventListener
{
    protected static $ref_ids = array();
    
    /**
    * Handle an event in a listener.
    *
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        /**
         * @var $post ilForumPost
         * @var $logger ilLogger
         */
        global $DIC;

        $logger = $DIC->logger()->frm();

        // 0 = no notifications, 1 = direct, 2 = cron job
        $immediate_notifications_enabled = $DIC->settings()->get('forum_notification', 0) == 1;

        switch ($a_component) {
            case 'Modules/Forum':
                switch ($a_event) {
                    case 'mergedThreads':
                        ilForumPostDraft::moveDraftsByMergedThreads($a_parameter['source_thread_id'], $a_parameter['target_thread_id']);
                        break;
                    case 'movedThreads':
                        ilForumPostDraft::moveDraftsByMovedThread($a_parameter['thread_ids'], $a_parameter['source_ref_id'], $a_parameter['target_ref_id']);
                        break;
                    case 'createdPost':
                        $post              = $a_parameter['post'];
                        $notify_moderators = $a_parameter['notify_moderators'];

                        $logger->debug(sprintf(
                            "Received event '%s' for posting with id %s (subject: %s|ref_id: %s)",
                            $a_event,
                            $post->getId(),
                            $post->getSubject(),
                            $a_parameter['ref_id']
                        ));

                        $provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());

                        if ($immediate_notifications_enabled && $post->isActivated()) {
                            $logger->debug(
                                'Immediate notification delivery is enabled, posting is already published: ' .
                                'Delegating "New Posting" notifications ...'
                            );

                            self::delegateNotification(
                                $provider,
                                ilForumMailNotification::TYPE_POST_NEW,
                                $logger
                            );
                        } elseif ($post->isActivated()) {
                            $logger->debug(
                                'Immediate notification delivery is disabled, posting is already published: ' .
                                '"New Posting" notifications will be send via cron job (if enabled) ...'
                            );
                        }

                        // This notification will be NOT send via cron job
                        if ($notify_moderators && !$post->isActivated()) {
                            $logger->debug(
                                'Posting is not published, moderators have to be notified: ' .
                                'Delegating "Activate Posting" notifications ...'
                            );

                            self::delegateNotification(
                                $provider,
                                ilForumMailNotification::TYPE_POST_ACTIVATION,
                                $logger
                            );
                        } else {
                            $logger->debug(
                                'Posting is already published or forum postings will be automatically published: ' .
                                'No "Activate Posting" notifications will be send ...'
                            );
                        }

                        // If the author of the parent post wants to be notified and the author of the new post is not the same individual: Send a message
                        // This notification will be NOT send via cron job
                        if ($immediate_notifications_enabled || ilCronManager::isJobActive('frm_notification')) {
                            if ($post->isActivated() && $post->getParentId() > 0) {
                                $parent_post = new ilForumPost($post->getParentId());
                                if (
                                    $parent_post->isNotificationEnabled() &&
                                    $parent_post->getPosAuthorId() != $post->getPosAuthorId()
                                ) {
                                    $logger->debug(
                                        'Author of parent posting wants to be notified: ' .
                                        'Delegating "Reply to Posting" notification ...'
                                    );

                                    self::delegateNotification(
                                        $provider,
                                        ilForumMailNotification::TYPE_POST_ANSWERED,
                                        $logger
                                    );
                                } else {
                                    $logger->debug(
                                        'Author of parent posting does not want to be notified, or both authors are ' .
                                        'identical: ' .
                                        'No "Reply to Posting" notification will be send ...'
                                    );
                                }
                            } else {
                                $logger->debug(
                                    'Posting is not published yet or it is the root posting: ' .
                                    'No "Reply to Posting" notification will be send ...'
                                );
                            }
                        } else {
                            $logger->debug(
                                'Neither immediate notifications, nor notifications via cron job are enabled: ' .
                                'No "Reply to Posting" notification will be send at all ...'
                            );
                        }
                        break;

                    case 'activatedPost':
                        $post = $a_parameter['post'];

                        $logger->debug(sprintf(
                            "Received event '%s' for posting with id %s (subject: %s|ref_id: %s)",
                            $a_event,
                            $post->getId(),
                            $post->getSubject(),
                            $a_parameter['ref_id']
                        ));

                        if ($immediate_notifications_enabled && $post->isActivated()) {
                            $provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());

                            $logger->debug(
                                'Immediate notification delivery is enabled, posting is already published: ' .
                                'Delegating "New Posting" notifications ...'
                            );

                            self::delegateNotification(
                                $provider,
                                ilForumMailNotification::TYPE_POST_NEW,
                                $logger
                            );
                        } elseif ($post->isActivated()) {
                            $logger->debug(
                                'Immediate notification delivery is disabled, posting is already published: ' .
                                '"New Posting" notifications will be send via cron job (if enabled) ...'
                            );
                        }

                        // TODO Maybe an email regarding the parent posting's author notification is missing here
                        break;

                    case 'updatedPost':
                        $post              = $a_parameter['post'];
                        $notify_moderators = $a_parameter['notify_moderators'];

                        $logger->debug(sprintf(
                            "Received event '%s' for posting with id %s (subject: %s|ref_id: %s)",
                            $a_event,
                            $post->getId(),
                            $post->getSubject(),
                            $a_parameter['ref_id']
                        ));

                        if (!$a_parameter['old_status_was_active']) {
                            $logger->debug(
                                'Posting was "inactive" before raising event: ' .
                                'No "Modified Posting" or "Posting Published" notifications will be send ...'
                            );
                            return;
                        }

                        $provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());

                        if ($immediate_notifications_enabled && $post->isActivated()) {
                            $logger->debug(
                                'Immediate notification delivery is enabled, posting is already published: ' .
                                'Delegating "Modified Posting" notifications ...'
                            );
                            
                            self::delegateNotification(
                                $provider,
                                ilForumMailNotification::TYPE_POST_UPDATED,
                                $logger
                            );
                        } elseif ($post->isActivated()) {
                            $logger->debug(
                                'Immediate notification delivery is disabled, posting is already published: ' .
                                '"Modified Posting" notifications will be send via cron job (if enabled) ...'
                            );
                        }

                        // This notification will be NOT send via cron job
                        if ($notify_moderators && !$post->isActivated()) {
                            $logger->debug(
                                'Posting is not published, moderators have to be notified: ' .
                                'Delegating immediate "Activate Posting" notifications ...'
                            );

                            self::delegateNotification(
                                $provider,
                                ilForumMailNotification::TYPE_POST_ACTIVATION,
                                $logger
                            );
                        } else {
                            $logger->debug(
                                'Posting is already published or forum postings will be automatically published: ' .
                                'No "Activate Posting" notifications will be send ...'
                            );
                        }
                        break;

                    case 'censoredPost':
                        $post = $a_parameter['post'];

                        $logger->debug(sprintf(
                            "Received event '%s' for posting with id %s (subject: %s|ref_id: %s)",
                            $a_event,
                            $post->getId(),
                            $post->getSubject(),
                            $a_parameter['ref_id']
                        ));

                        if ($immediate_notifications_enabled) {
                            $provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());
                            if ($post->isCensored() && $post->isActivated()) {
                                $logger->debug(
                                    'Immediate notification delivery is enabled, posting is already published and ' .
                                    'now censored: ' .
                                    'Delegating "Posting Censored" notifications ...'
                                );

                                self::delegateNotification(
                                    $provider,
                                    ilForumMailNotification::TYPE_POST_CENSORED,
                                    $logger
                                );
                            } elseif (!$post->isCensored() && $post->isActivated()) {
                                $logger->debug(
                                    'Immediate notification delivery is enabled, posting is already published and ' .
                                    'censorship has been revoked: ' .
                                    'Delegating "Censorship Revoked" notifications ...'
                                );

                                self::delegateNotification(
                                    $provider,
                                    ilForumMailNotification::TYPE_POST_UNCENSORED,
                                    $logger
                                );
                            } else {
                                $logger->debug(
                                    'Posting is not published: ' .
                                    '"Posting Censored" or "Censorship Revoked" notifications will not be send ...'
                                );
                            }
                        } else {
                            $logger->debug(
                                'Immediate notification delivery is disabled: ' .
                                '"Posting Censored" notifications will be send via cron job (if enabled) ...'
                            );
                        }
                        break;

                    case 'deletedPost':
                        $post = $a_parameter['post'];

                        $logger->debug(sprintf(
                            "Received event '%s' for posting with id %s (subject: %s|ref_id: %s)",
                            $a_event,
                            $post->getId(),
                            $post->getSubject(),
                            $a_parameter['ref_id']
                        ));

                        $thread_deleted = $a_parameter['thread_deleted'];

                        $provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());

                        if ($post->isActivated()) {
                            if (ilCronManager::isJobActive('frm_notification')) {
                                $logger->debug(
                                    'Notification delivery via cron job is enabled: ' .
                                    'Storing posting data for deferred "Posting/Thread Deleted" notifications ...'
                                );

                                $delObj = new ilForumPostsDeleted($provider);
                                $delObj->setThreadDeleted($thread_deleted);
                                $delObj->insert();
                            } elseif ($immediate_notifications_enabled) {
                                $notificationType = ilForumMailNotification::TYPE_POST_DELETED;
                                if ($thread_deleted) {
                                    $notificationType = ilForumMailNotification::TYPE_THREAD_DELETED;

                                    $logger->debug(
                                        'Immediate notification delivery is enabled : ' .
                                        'Delegating "Thread Deleted" notifications ...'
                                    );
                                } else {
                                    $logger->debug(
                                        'Immediate notification delivery is enabled : ' .
                                        'Delegating "Posting Deleted" notifications ...'
                                    );
                                }

                                self::delegateNotification(
                                    $provider,
                                    $notificationType,
                                    $logger
                                );
                            }
                        } else {
                            $logger->debug(
                                'Posting is not published: ' .
                                '"Posting Deleted" or "Thread Deleted" notifications will not be send ...'
                            );
                        }
                        break;
                    case 'savedAsDraft':
                    case 'updatedDraft':
                    case 'deletedDraft':
                        /**
                         * var $draftObj ilForumPostDraft
                         */
                        $draftObj   = $a_parameter['draftObj'];

                        $historyObj = new ilForumDraftsHistory();
                        $historyObj->deleteHistoryByDraftIds(array($draftObj->getDraftId()));
                        
                        break;
                    case 'publishedDraft':
                        /**
                         * var $draftObj ilForumPostDraft
                         */
                        $draftObj   = $a_parameter['draftObj'];

                        $historyObj = new ilForumDraftsHistory();
                        $historyObj->deleteHistoryByDraftIds(array($draftObj->getDraftId()));
                        
                        ilForumPostDraft::deleteMobsOfDraft($draftObj->getDraftId());
                        
                        break;
                }
                break;
            case "Services/News":
                switch ($a_event) {
                    case "readNews":
                        // here we could set postings to read, if news is
                        // read (has to be implemented)
                        break;
                }
                break;

            case "Services/Tree":
                switch ($a_event) {
                    case "moveTree":
                        ilForumNotification::_clearForcedForumNotifications($a_parameter);
                        break;
                }
                break;
            
            case "Modules/Course":
                switch ($a_event) {
                    case "addParticipant":
                        $ref_ids = self::getCachedReferences($a_parameter['obj_id']);

                        foreach ($ref_ids as $ref_id) {
                            ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
                            break;
                        }
                        
                        break;
                    case 'deleteParticipant':
                        $ref_ids = self::getCachedReferences($a_parameter['obj_id']);

                        foreach ($ref_ids as $ref_id) {
                            ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
                            break;
                        }
                        break;
                }
                break;
            case "Modules/Group":
                switch ($a_event) {
                    case "addParticipant":
                        $ref_ids = self::getCachedReferences($a_parameter['obj_id']);

                        foreach ($ref_ids as $ref_id) {
                            ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
                            break;
                        }

                        break;
                    case 'deleteParticipant':
                        $ref_ids = self::getCachedReferences($a_parameter['obj_id']);

                        foreach ($ref_ids as $ref_id) {
                            ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
                            break;
                        }
                        break;
                }
                break;
            case 'Services/User':
                switch ($a_event) {
                    case 'deleteUser':
                        ilForumPostDraft::deleteDraftsByUserId($a_parameter['usr_id']);
                        break;
                }
                break;
        }
    }

    /**
     * @param int $obj_id
     */
    private static function getCachedReferences($obj_id)
    {
        if (!array_key_exists($obj_id, self::$ref_ids)) {
            self::$ref_ids[$obj_id] = ilObject::_getAllReferences($obj_id);
        }
        return self::$ref_ids[$obj_id];
    }

    /**
     * @param ilObjForumNotificationDataProvider $provider
     * @param int                                $notification_type
     * @param ilLogger                           $logger
     */
    private static function delegateNotification(
        ilObjForumNotificationDataProvider $provider,
        $notification_type,
        \ilLogger $logger
    ) {
        switch ($notification_type) {
            case ilForumMailNotification::TYPE_POST_ACTIVATION:
                self::sendNotification($provider, $logger, $notification_type, $provider->getPostActivationRecipients());
                break;

            case ilForumMailNotification::TYPE_POST_ANSWERED:
                self::sendNotification($provider, $logger, $notification_type, $provider->getPostAnsweredRecipients());
                break;

            default:
                // get recipients who wants to get forum notifications
                $logger->debug('Determining subscribers for global forum notifications ...');
                $frm_recipients = $provider->getForumNotificationRecipients();

                // get recipients who wants to get thread notifications
                $logger->debug('Determining subscribers for thread notifications ...');
                $thread_recipients = $provider->getThreadNotificationRecipients();

                $recipients = array_unique(array_merge($frm_recipients, $thread_recipients));
                self::sendNotification($provider, $logger, $notification_type, $recipients);

                break;
        }
    }

    /**
     * @param ilObjForumNotificationDataProvider $provider
     * @param ilLogger $logger
     * @param int $notificationTypes
     * @param array $recipients
     */
    public static function sendNotification(
        ilObjForumNotificationDataProvider $provider,
        \ilLogger $logger,
        int $notificationTypes,
        array $recipients
    ) {
        if (count($recipients)) {
            $logger->debug(sprintf(
                'Will send %s notification(s) to: %s',
                count($recipients),
                implode(', ', $recipients)
            ));

            $mailNotification = new ilForumMailNotification($provider, $logger);
            $mailNotification->setType($notificationTypes);
            $mailNotification->setRecipients($recipients);
            $mailNotification->send();
        } else {
            $logger->debug('No recipients found, skipped notification delivery.');
        }
    }
}
