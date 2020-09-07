<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Forum notifications
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumCronNotification extends ilCronJob
{
    const KEEP_ALIVE_CHUNK_SIZE = 25;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var \ilLogger
     */
    protected $logger;

    /**
     * @var \ilForumCronNotificationDataProvider[]
     */
    public static $providerObject = array();

    /**
     * @var array frm_posts_deleted.deleted_id
     */
    protected static $deleted_ids_cache = array();

    /**
     * @var array
     */
    protected static $ref_ids_by_obj_id = array();

    /**
     * @var array
     */
    protected static $accessible_ref_ids_by_user = array();
    
    /**
     * @var int
     */
    protected $num_sent_messages = 0;

    /** @var \ilDBInterface */
    private $ilDB;

    /** @var \ilForumNotificationCache|null */
    private $notificationCache;

    /**
     * @param ilDBInterface|null $database
     * @param ilForumNotificationCache|null $notificationCache
     */
    public function __construct(\ilDBInterface $database = null, \ilForumNotificationCache $notificationCache = null)
    {
        $this->settings = new ilSetting('frma');

        if ($database === null) {
            global $DIC;
            $ilDB = $DIC->database();
        }
        $this->ilDB = $ilDB;

        if ($notificationCache === null) {
            $notificationCache = new \ilForumNotificationCache();
        }
        $this->notificationCache = $notificationCache;
    }

    public function getId()
    {
        return "frm_notification";
    }
    
    public function getTitle()
    {
        global $DIC;

        return $DIC->language()->txt("cron_forum_notification");
    }
    
    public function getDescription()
    {
        global $DIC;

        return $DIC->language()->txt("cron_forum_notification_crob_desc");
    }
    
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }
    
    public function getDefaultScheduleValue()
    {
        return 1;
    }
    
    public function hasAutoActivation()
    {
        return false;
    }
    
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasCustomSettings()
    {
        return true;
    }

    /**
     *
     */
    public function keepAlive()
    {
        $this->logger->debug('Sending ping to cron manager ...');
        \ilCronManager::ping($this->getId());
        $this->logger->debug(sprintf('Current memory usage: %s', memory_get_usage(true)));
    }

    /**
     * @return ilCronJobResult
     */
    public function run()
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        $lng = $DIC->language();

        $this->logger = $DIC->logger()->frm();

        $status = ilCronJobResult::STATUS_NO_ACTION;

        $lng->loadLanguageModule('forum');

        $this->logger->info('Started forum notification job ...');

        if (!($last_run_datetime = $ilSetting->get('cron_forum_notification_last_date'))) {
            $last_run_datetime = null;
        }

        $this->num_sent_messages = 0;
        $cj_start_date = date('Y-m-d H:i:s');

        if ($last_run_datetime != null &&
            checkDate(date('m', strtotime($last_run_datetime)), date('d', strtotime($last_run_datetime)), date('Y', strtotime($last_run_datetime)))) {
            $threshold = max(strtotime($last_run_datetime), strtotime('-' . (int) $this->settings->get('max_notification_age', 30) . ' days', time()));
        } else {
            $threshold = strtotime('-' . (int) $this->settings->get('max_notification_age', 30) . ' days', time());
        }

        $this->logger->info(sprintf('Threshold for forum event determination is: %s', date('Y-m-d H:i:s', $threshold)));

        $threshold_date = date('Y-m-d H:i:s', $threshold);

        $this->sendNotificationForNewPosts($threshold_date);

        $this->sendNotificationForUpdatedPosts($threshold_date);

        $this->sendNotificationForCensoredPosts($threshold_date);

        $this->sendNotificationForUncensoredPosts($threshold_date);

        $this->sendNotificationForDeletedThreads();

        $this->sendNotifcationForDeletedPosts();

        $ilSetting->set('cron_forum_notification_last_date', $cj_start_date);

        $mess = 'Sent ' . $this->num_sent_messages . ' messages.';

        $this->logger->info($mess);
        $this->logger->info('Finished forum notification job');

        $result = new ilCronJobResult();
        if ($this->num_sent_messages) {
            $status = ilCronJobResult::STATUS_OK;
            $result->setMessage($mess);
        };
        $result->setStatus($status);
        return $result;
    }

    /**
     * @param int $a_obj_id
     * @return array
     */
    protected function getRefIdsByObjId($a_obj_id)
    {
        if (!array_key_exists($a_obj_id, self::$ref_ids_by_obj_id)) {
            self::$ref_ids_by_obj_id[$a_obj_id] = ilObject::_getAllReferences($a_obj_id);
        }

        return (array) self::$ref_ids_by_obj_id[$a_obj_id];
    }

    /**
     * @param int $a_user_id
     * @param int $a_obj_id
     * @return int
     */
    protected function getFirstAccessibleRefIdBUserAndObjId($a_user_id, $a_obj_id)
    {
        global $DIC;
        $ilAccess = $DIC->access();

        if (!array_key_exists($a_user_id, self::$accessible_ref_ids_by_user)) {
            self::$accessible_ref_ids_by_user[$a_user_id] = array();
        }

        if (!array_key_exists($a_obj_id, self::$accessible_ref_ids_by_user[$a_user_id])) {
            $accessible_ref_id = 0;
            foreach ($this->getRefIdsByObjId($a_obj_id) as $ref_id) {
                if ($ilAccess->checkAccessOfUser($a_user_id, 'read', '', $ref_id)) {
                    $accessible_ref_id = $ref_id;
                    break;
                }
            }
            self::$accessible_ref_ids_by_user[$a_user_id][$a_obj_id] = $accessible_ref_id;
        }

        return (int) self::$accessible_ref_ids_by_user[$a_user_id][$a_obj_id];
    }

    /**
     * @param $res
     * @param $notification_type
     */
    public function sendCronForumNotification($res, $notification_type)
    {
        global $DIC;
        $ilDB = $DIC->database();

        while ($row = $ilDB->fetchAssoc($res)) {
            if ($notification_type == ilForumMailNotification::TYPE_POST_DELETED
                || $notification_type == ilForumMailNotification::TYPE_THREAD_DELETED) {
                // important! save the deleted_id to cache before proceeding getFirstAccessibleRefIdBUserAndObjId !
                self::$deleted_ids_cache[$row['deleted_id']] = $row['deleted_id'];
            }
            
            $ref_id = $this->getFirstAccessibleRefIdBUserAndObjId($row['user_id'], $row['obj_id']);
            if ($ref_id < 1) {
                $this->logger->debug(sprintf(
                    'The recipient with id %s has no "read" permission for object with id %s',
                    $row['user_id'],
                    $row['obj_id']
                ));
                continue;
            }

            $row['ref_id'] = $ref_id;

            if ($this->existsProviderObject($row['pos_pk'])) {
                self::$providerObject[$row['pos_pk']]->addRecipient($row['user_id']);
            } else {
                $this->addProviderObject($row);
            }
        }

        $usrIdsToPreload = array();
        foreach (self::$providerObject as $provider) {
            if ($provider->getPosAuthorId()) {
                $usrIdsToPreload[$provider->getPosAuthorId()] = $provider->getPosAuthorId();
            }
            if ($provider->getPosDisplayUserId()) {
                $usrIdsToPreload[$provider->getPosDisplayUserId()] = $provider->getPosDisplayUserId();
            }
            if ($provider->getPostUpdateUserId()) {
                $usrIdsToPreload[$provider->getPostUpdateUserId()] = $provider->getPostUpdateUserId();
            }
        }

        ilForumAuthorInformationCache::preloadUserObjects(array_unique($usrIdsToPreload));

        $i = 0;
        foreach (self::$providerObject as $provider) {
            if ($i > 0 && ($i % self::KEEP_ALIVE_CHUNK_SIZE) == 0) {
                $this->keepAlive();
            }

            $recipients = array_unique($provider->getCronRecipients());

            $this->logger->info(sprintf(
                'Trying to send forum notifications for posting id "%s", type "%s" and recipients: %s',
                $provider->getPostId(),
                $notification_type,
                implode(', ', $recipients)
            ));

            $mailNotification = new ilForumMailNotification($provider, $this->logger);
            $mailNotification->setIsCronjob(true);
            $mailNotification->setType($notification_type);
            $mailNotification->setRecipients($recipients);

            $mailNotification->send();

            $this->num_sent_messages += count($provider->getCronRecipients());
            $this->logger->info(sprintf("Sent notifications ... "));

            ++$i;
        }
        
        $this->resetProviderCache();
    }

    /**
     * @param $post_id
     * @return bool
     */
    public function existsProviderObject($post_id)
    {
        if (isset(self::$providerObject[$post_id])) {
            return true;
        }
        return false;
    }

    /**
     * @param $row
     */
    private function addProviderObject($row)
    {
        $tmp_provider = new ilForumCronNotificationDataProvider($row, $this->notificationCache);

        self::$providerObject[$row['pos_pk']] = $tmp_provider;
        self::$providerObject[$row['pos_pk']]->addRecipient($row['user_id']);
    }

    /**
     *
     */
    private function resetProviderCache()
    {
        self::$providerObject = array();
    }
    
    /**
     * @param int   $a_form_id
     * @param array $a_fields
     * @param bool  $a_is_active
     */
    public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
    {
        global $DIC;
        $lng = $DIC->language();

        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_FORUM:
                $a_fields['cron_forum_notification'] = $a_is_active ?
                    $lng->txt('enabled') :
                    $lng->txt('disabled');
                break;
        }
    }

    /**
     * @param bool $a_currently_active
     */
    public function activationWasToggled($a_currently_active)
    {
        global $DIC;

        $value = 1;
        // propagate cron-job setting to object setting
        if ((bool) $a_currently_active) {
            $value = 2;
        }
        $DIC->settings()->set('forum_notification', $value);
    }

    /**
     * @param ilPropertyFormGUI $a_form
     */
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        global $DIC;
        $lng = $DIC->language();

        $lng->loadLanguageModule('forum');

        $max_notification_age = new ilNumberInputGUI($lng->txt('frm_max_notification_age'), 'max_notification_age');
        $max_notification_age->setSize(5);
        $max_notification_age->setSuffix($lng->txt('frm_max_notification_age_unit'));
        $max_notification_age->setRequired(true);
        $max_notification_age->allowDecimals(false);
        $max_notification_age->setMinValue(1);
        $max_notification_age->setInfo($lng->txt('frm_max_notification_age_info'));
        $max_notification_age->setValue($this->settings->get('max_notification_age', 30));

        $a_form->addItem($max_notification_age);
    }

    /**
     * @param ilPropertyFormGUI $a_form
     * @return bool
     */
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        $this->settings->set('max_notification_age', $a_form->getInput('max_notification_age'));
        return true;
    }

    /**
     * @param $threshold_date
     */
    private function sendNotificationForNewPosts(string $threshold_date)
    {
        $condition = '
			frm_posts.pos_status = %s AND (
				(frm_posts.pos_date >= %s AND frm_posts.pos_date = frm_posts.pos_activation_date) OR 
				(frm_posts.pos_activation_date >= %s AND frm_posts.pos_date < frm_posts.pos_activation_date)
			) ';
        $types = array('integer', 'timestamp', 'timestamp');
        $values = array(1, $threshold_date, $threshold_date);

        $res = $this->ilDB->queryf(
            $this->createForumPostSql($condition),
            $types,
            $values
        );

        $this->sendNotification(
            $res,
            'new posting',
            ilForumMailNotification::TYPE_POST_NEW
        );
    }

    /**
     * @param $threshold_date
     */
    private function sendNotificationForUpdatedPosts(string $threshold_date)
    {
        $condition = '
			frm_posts.pos_cens = %s AND frm_posts.pos_status = %s AND 
			(frm_posts.pos_update > frm_posts.pos_date AND frm_posts.pos_update >= %s) ';
        $types = array('integer', 'integer', 'timestamp');
        $values = array(0, 1, $threshold_date);

        $res = $this->ilDB->queryf(
            $this->createForumPostSql($condition),
            $types,
            $values
        );

        $this->sendNotification(
            $res,
            'updated posting',
            ilForumMailNotification::TYPE_POST_UPDATED
        );
    }

    /**
     * @param $threshold_date
     */
    private function sendNotificationForCensoredPosts(string $threshold_date)
    {
        $condition = '
			frm_posts.pos_cens = %s AND frm_posts.pos_status = %s AND  
            (frm_posts.pos_cens_date >= %s AND frm_posts.pos_cens_date > frm_posts.pos_activation_date ) ';
        $types = array('integer', 'integer', 'timestamp');
        $values = array(1, 1, $threshold_date);

        $res = $this->ilDB->queryf(
            $this->createForumPostSql($condition),
            $types,
            $values
        );

        $this->sendNotification(
            $res,
            'censored posting',
            ilForumMailNotification::TYPE_POST_CENSORED
        );
    }

    /**
     * @param $threshold_date
     */
    private function sendNotificationForUncensoredPosts(string $threshold_date)
    {
        $condition = '
			frm_posts.pos_cens = %s AND frm_posts.pos_status = %s AND  
            (frm_posts.pos_cens_date >= %s AND frm_posts.pos_cens_date > frm_posts.pos_activation_date ) ';
        $types = array('integer', 'integer', 'timestamp');
        $values = array(0, 1, $threshold_date);

        $res = $this->ilDB->queryf(
            $this->createForumPostSql($condition),
            $types,
            $values
        );

        $this->sendNotification(
            $res,
            'uncensored posting',
            ilForumMailNotification::TYPE_POST_UNCENSORED
        );
    }

    private function sendNotificationForDeletedThreads()
    {
        $res = $this->ilDB->queryF(
            $this->createSelectOfDeletionNotificationsSql(),
            array('integer'),
            array(1)
        );

        $this->sendDeleteNotifcations(
            $res,
            'frm_threads_deleted',
            'deleted threads',
            ilForumMailNotification::TYPE_THREAD_DELETED
        );
    }

    private function sendNotifcationForDeletedPosts()
    {
        $res = $this->ilDB->queryF(
            $this->createSelectOfDeletionNotificationsSql(),
            array('integer'),
            array(0)
        );

        $this->sendDeleteNotifcations(
            $res,
            'frm_posts_deleted',
            'deleted postings',
            ilForumMailNotification::TYPE_POST_DELETED
        );
    }

    /**
     * @param $res
     * @param $actionName
     * @param $notificationType
     */
    private function sendNotification(\ilPDOStatement $res, string $actionName, int $notificationType)
    {
        $numRows = $this->ilDB->numRows($res);
        if ($numRows > 0) {
            $this->logger->info(sprintf('Sending notifications for %s "%s" events ...', $numRows, $actionName));
            $this->sendCronForumNotification($res, $notificationType);
            $this->logger->info(sprintf('Sent notifications for %s ...', $actionName));
        }

        $this->keepAlive();
    }

    /**
     * @param $res
     * @param $action
     * @param $actionDescription
     * @param $notificationType
     */
    private function sendDeleteNotifcations(\ilPDOStatement $res, string $action, string $actionDescription, int $notificationType)
    {
        $numRows = $this->ilDB->numRows($res);
        if ($numRows > 0) {
            $this->logger->info(sprintf('Sending notifications for %s "%s" events ...', $numRows, $actionDescription));
            $this->sendCronForumNotification($res, $notificationType);
            if (count(self::$deleted_ids_cache) > 0) {
                $this->ilDB->manipulate('DELETE FROM frm_posts_deleted WHERE ' . $this->ilDB->in('deleted_id', self::$deleted_ids_cache, false, 'integer'));
                $this->logger->info('Deleted obsolete entries of table "' . $action . '" ...');
            }
            $this->logger->info(sprintf('Sent notifications for %s ...', $actionDescription));
        }

        $this->keepAlive();
    }

    /**
     * @param $condition
     * @return string
     */
    private function createForumPostSql($condition) : string
    {
        return '
			SELECT 	frm_threads.thr_subject thr_subject,
					frm_data.top_name top_name,
					frm_data.top_frm_fk obj_id,
					frm_notification.user_id user_id,
					frm_threads.thr_pk thread_id,
					frm_posts.*
			FROM 	frm_notification, frm_posts, frm_threads, frm_data, frm_posts_tree 
			WHERE	frm_posts.pos_thr_fk = frm_threads.thr_pk AND ' . $condition . '
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	frm_posts.pos_display_user_id != frm_notification.user_id
			AND     frm_posts_tree.pos_fk = frm_posts.pos_pk AND frm_posts_tree.parent_pos != 0
			ORDER BY frm_posts.pos_date ASC';
    }

    /**
     * @return string
     */
    private function createSelectOfDeletionNotificationsSql() : string
    {
        return '
			SELECT 	frm_posts_deleted.thread_title thr_subject,
					frm_posts_deleted.forum_title  top_name,
					frm_posts_deleted.obj_id obj_id,
					frm_notification.user_id user_id,
					frm_posts_deleted.pos_display_user_id,
					frm_posts_deleted.pos_usr_alias,
					frm_posts_deleted.deleted_id,
					frm_posts_deleted.post_date pos_date,
					frm_posts_deleted.post_title pos_subject,
					frm_posts_deleted.post_message pos_message,
					frm_posts_deleted.deleted_by
					
			FROM 	frm_notification, frm_posts_deleted
			
			WHERE 	( frm_posts_deleted.obj_id = frm_notification.frm_id
					OR frm_posts_deleted.thread_id = frm_notification.thread_id)
			AND 	frm_posts_deleted.pos_display_user_id != frm_notification.user_id
			AND 	frm_posts_deleted.is_thread_deleted = %s
			ORDER BY frm_posts_deleted.post_date ASC';
    }
}
