<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/EventHandling/interfaces/interface.ilAppEventListener.php';
require_once './Modules/Forum/classes/class.ilForumNotificationCache.php';


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
	static function handleEvent($a_component, $a_event, $a_parameter)
	{
		/**
		 * @var $post ilForumPost
		 */
		global $DIC;

		$logger = $DIC->logger()->frm();

		// 0 = no notifications, 1 = direct, 2 = cron job
		$immediate_notifications_enabled = $DIC->settings()->get('forum_notification', 0) == 1;

		switch($a_component)
		{
			case 'Modules/Forum':
				switch($a_event)
				{
					case 'mergedThreads':
						include_once './Modules/Forum/classes/class.ilForumPostDraft.php';
						ilForumPostDraft::moveDraftsByMergedThreads($a_parameter['source_thread_id'], $a_parameter['target_thread_id']);
						break;
					case 'movedThreads':
						ilForumPostDraft::moveDraftsByMovedThread($a_parameter['thread_ids'], $a_parameter['source_ref_id'], $a_parameter['target_ref_id']);
						break;
					case 'createdPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';
						require_once 'Services/Cron/classes/class.ilCronManager.php';

						$post              = $a_parameter['post'];
						$notify_moderators = $a_parameter['notify_moderators'];

						$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());

						if($immediate_notifications_enabled && $post->isActivated())
						{
							self::delegateNotification(
								$provider,
								ilForumMailNotification::TYPE_POST_NEW,
								$logger
							);
						}

						if($notify_moderators && !$post->isActivated())
						{
							self::delegateNotification(
								$provider,
								ilForumMailNotification::TYPE_POST_ACTIVATION,
								$logger
							);
						}

						// If the author of the parent post wants to be notified and the author of the new post is not the same individual: Send a message
						// This is the only notification which is not send via cron job
						if($immediate_notifications_enabled || ilCronManager::isJobActive('frm_notification'))
						{
							if($post->isActivated() && $post->getParentId() > 0)
							{
								$parent_post = new ilForumPost($post->getParentId());
								if($parent_post->isNotificationEnabled() && $parent_post->getPosAuthorId() != $post->getPosAuthorId())
								{
									self::delegateNotification(
										$provider,
										ilForumMailNotification::TYPE_POST_ANSWERED,
										$logger
									);
								}
							}
						}
						break;

					case 'activatedPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';
						require_once 'Services/Cron/classes/class.ilCronManager.php';
						
						$post = $a_parameter['post'];
						if($immediate_notifications_enabled && $post->isActivated())
						{
							$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());
							self::delegateNotification(
								$provider,
								ilForumMailNotification::TYPE_POST_NEW,
								$logger
							);
						}
						break;

					case 'updatedPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';
						
						if(!$a_parameter['old_status_was_active'])
						{
							return;
						}

						$post              = $a_parameter['post'];
						$notify_moderators = $a_parameter['notify_moderators'];

						$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());

						if($immediate_notifications_enabled && $post->isActivated())
						{
							self::delegateNotification(
								$provider,
								ilForumMailNotification::TYPE_POST_UPDATED,
								$logger
							);
						}

						if($notify_moderators && !$post->isActivated())
						{
							self::delegateNotification(
								$provider,
								ilForumMailNotification::TYPE_POST_ACTIVATION,
								$logger
							);
						}
						break;

					case 'censoredPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';

						$post = $a_parameter['post'];

						if($immediate_notifications_enabled)
						{
							$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());
							if($post->isCensored() && $post->isActivated())
							{
								self::delegateNotification(
									$provider,
									ilForumMailNotification::TYPE_POST_CENSORED,
									$logger
								);
							}
							else if(!$post->isCensored() && $post->isActivated())
							{
								self::delegateNotification(
									$provider,
									ilForumMailNotification::TYPE_POST_UNCENSORED,
									$logger
								);
							}
						}
						break;

					case 'deletedPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';
						require_once 'Services/Cron/classes/class.ilCronManager.php';
						
						$post = $a_parameter['post'];

						$thread_deleted = $a_parameter['thread_deleted'];

						$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id'], new ilForumNotificationCache());

						if($post->isActivated())
						{
							if(ilCronManager::isJobActive('frm_notification'))
							{
								require_once 'Modules/Forum/classes/class.ilForumPostsDeleted.php';
								$delObj = new ilForumPostsDeleted($provider);
								$delObj->setThreadDeleted($thread_deleted);
								$delObj->insert();
							}
							else if($immediate_notifications_enabled)
							{
								$notificationType = ilForumMailNotification::TYPE_POST_DELETED;
								if($thread_deleted)
								{
									$notificationType = ilForumMailNotification::TYPE_THREAD_DELETED;
								}

								self::delegateNotification(
									$provider,
									$notificationType,
									$logger
								);
							}
						}
						break;
					case 'savedAsDraft':
					case 'updatedDraft':
					case 'deletedDraft':
						require_once './Modules/Forum/classes/class.ilForumDraftsHistory.php';
						
						/**
						 * var $draftObj ilForumPostDraft
						 */
						$draftObj   = $a_parameter['draftObj'];

						$historyObj = new ilForumDraftsHistory();
						$historyObj->deleteHistoryByDraftIds(array($draftObj->getDraftId()));
						
						break;
					case 'publishedDraft':
						require_once './Modules/Forum/classes/class.ilForumDraftsHistory.php';
						require_once './Modules/Forum/classes/class.ilForumPostDraft.php';
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
				switch ($a_event)
				{
					case "readNews":
						// here we could set postings to read, if news is
						// read (has to be implemented)
						break;
				}
				break;

			case "Services/Tree":
				switch ($a_event)
				{
					case "moveTree":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';
						ilForumNotification::_clearForcedForumNotifications($a_parameter);
						break;
				}
				break;
			
			case "Modules/Course":
				switch($a_event)
				{
					case "addParticipant":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';
						
						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
							break;
						}
						
						break;
					case 'deleteParticipant':
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
							break;
						}
						break;
				}
				break;
			case "Modules/Group":
				switch($a_event)
				{
					case "addParticipant":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
							break;
						}

						break;
					case 'deleteParticipant':
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
							break;
						}
						break;
				}
				break;
			case 'Services/User':
				switch($a_event)
				{
					case 'deleteUser':  
						include_once './Modules/Forum/classes/class.ilForumPostDraft.php';
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
		if(!array_key_exists($obj_id, self::$ref_ids))
		{
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
		switch($notification_type)
		{
			case ilForumMailNotification::TYPE_POST_ACTIVATION:
				self::sendNotification($provider, $logger, $notification_type, $provider->getPostActivationRecipients());
				break;

			case ilForumMailNotification::TYPE_POST_ANSWERED:
				self::sendNotification($provider, $logger, $notification_type, $provider->getPostAnsweredRecipients());
				break;

			default:
				// get recipients who wants to get forum notifications
				self::sendNotification($provider, $logger, $notification_type, $provider->getForumNotificationRecipients());

				// get recipients who wants to get thread notifications
				self::sendNotification($provider, $logger, $notification_type, $provider->getThreadNotificationRecipients());

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
		$mailNotification = new ilForumMailNotification($provider, $logger);
		$mailNotification->setType($notificationTypes);
		$mailNotification->setRecipients($recipients);
		$mailNotification->send();
	}
}
