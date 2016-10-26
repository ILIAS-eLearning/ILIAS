<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Forum listener. Listens to events of other components.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesForum
*/
class ilForumAppEventListener
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
		 * @var $ilSetting ilSetting
		 * @var $post ilForumPost
		 */
		global $ilSetting;

		// 0 = no notifications, 1 = direct, 2 = cron job
		$immediate_notifications_enabled = $ilSetting->get('forum_notification', 0) == 1;

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

						$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id']);

						if($immediate_notifications_enabled && $post->isActivated())
						{
							self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_NEW);
						}

						if($notify_moderators && !$post->isActivated())
						{
							self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_ACTIVATION);
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
									self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_ANSWERED);
								}
							}
						}
						break;

					case 'updatedPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';

						$post              = $a_parameter['post'];
						$notify_moderators = $a_parameter['notify_moderators'];

						$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id']);

						if($immediate_notifications_enabled && $post->isActivated())
						{
							self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_UPDATED);
						}

						if($notify_moderators && !$post->isActivated())
						{
							self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_ACTIVATION);
						}
						break;

					case 'censoredPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';

						$post = $a_parameter['post'];

						if($immediate_notifications_enabled)
						{
							$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id']);
							if($post->isCensored() && $post->isActivated())
							{
								self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_CENSORED);
							}
							else if(!$post->isCensored() && $post->isActivated())
							{
								self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_UNCENSORED);
							}
						}
						break;

					case 'deletedPost':
						require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
						require_once 'Modules/Forum/classes/class.ilObjForumNotificationDataProvider.php';
						require_once 'Services/Cron/classes/class.ilCronManager.php';
						
						$post = $a_parameter['post'];

						$thread_deleted = $a_parameter['thread_deleted'];

						$provider = new ilObjForumNotificationDataProvider($post, $a_parameter['ref_id']);

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
								if($thread_deleted)
								{
									self::delegateNotification($provider, ilForumMailNotification::TYPE_THREAD_DELETED);
								}
								else
								{
									self::delegateNotification($provider, ilForumMailNotification::TYPE_POST_DELETED);
								}
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
						$obj_id     = $a_parameter['obj_id'];
						$is_fileupload_allowed = (bool)$a_parameter['is_file_upload_allowed'];
						
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
						$obj_id     = $a_parameter['obj_id'];
						$is_fileupload_allowed = (bool)$a_parameter['is_file_upload_allowed'];
						
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
	 * @param 									 $notification_type
	 */
	private static function delegateNotification(ilObjForumNotificationDataProvider $provider, $notification_type)
	{
		switch($notification_type)
		{
			case ilForumMailNotification::TYPE_POST_ACTIVATION:
				$mailNotification = new ilForumMailNotification($provider);
				$mailNotification->setType($notification_type);
				$mailNotification->setRecipients($provider->getPostActivationRecipients());
				$mailNotification->send();
				break;

			case ilForumMailNotification::TYPE_POST_ANSWERED:
				$mailNotification = new ilForumMailNotification($provider);
				$mailNotification->setType($notification_type);
				$mailNotification->setRecipients($provider->getPostAnsweredRecipients());
				$mailNotification->send();
				break;

			default:
				// get recipients who wants to get forum notifications   
				$mailNotification = new ilForumMailNotification($provider);
				$mailNotification->setType($notification_type);
				$mailNotification->setRecipients($provider->getForumNotificationRecipients());
				$mailNotification->send();

				// get recipients who wants to get thread notifications
				$mailNotification = new ilForumMailNotification($provider);
				$mailNotification->setType($notification_type);
				$mailNotification->setRecipients($provider->getThreadNotificationRecipients());
				$mailNotification->send();
				break;
		}
	}
}