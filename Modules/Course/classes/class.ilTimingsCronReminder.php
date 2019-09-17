<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilTimingsCronReminder extends ilCronJob
{

	/**
	 * @var ilLogger
	 */
	protected $log;

	/**
	 * @var $lng ilLanguage
	 */
	protected $lng;

	/**
	 * @var $user_lang ilLanguage
	 */
	protected $user_lang;

	/**
	 * @var $ilDB ilDB
	 */
	protected $db;

	/**
	 * @var $ilObjDataCache ilObjectDataCache
	 */
	protected $obj_data_cache;

	/**
	 * @var array
	 */
	protected $users_with_exceeded_timings;

	/**
	 * @var array
	 */
	protected $users;

	/**
	 * @var array
	 */
	protected static $objects_information;

	/**
	 * @var array
	 */
	protected static $coaches_emails;

	/**
	 * @var int
	 */
	protected $now;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;
		global $ilObjDataCache, $lng, $ilDB;

		$this->log = $DIC->logger()->crs();
		$this->lng	= $DIC->language();
		$this->lng->loadLanguageModule('crs');
		$this->db	= $DIC->database();
		$this->obj_data_cache = $DIC['ilObjDataCache'];

		self::$objects_information         = array();
		self::$coaches_emails              = array();
		$this->users_with_exceeded_timings = array();
		$this->users                       = array();
		$this->now                         = time();

	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return 'crs_timings_reminder';
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->lng->txt('timings_reminder_notifications');
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->lng->txt('timings_reminder_notifications_info');
	}

	/**
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}

	public function getDefaultScheduleValue()
	{
		return;
	}

	/**
	 * @return bool
	 */
	public function hasAutoActivation()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function hasFlexibleSchedule()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function hasCustomSettings()
	{
		return false;
	}

	/**
	 * @return ilCronJobResult
	 */
	public function run()
	{
		$this->log->debug('Start.');

		$result = new ilCronJobResult();

		$this->gatherUsers();
		$this->gatherUsersWithExceededTimings();
		$this->getNewExceededObjectForUser();
		$this->getFreshlyStartedObjectsForUser();

		$result->setStatus(ilCronJobResult::STATUS_OK);

		$this->log->debug('End');

		return $result;
	}

	/**
	 * Read all active users
	 */
	protected function gatherUsers()
	{
		$now = time();
		$query = $this->db->queryF('SELECT usr_id FROM usr_data WHERE 
									(active = 1 AND time_limit_unlimited = 1) OR 
									(active = 1 AND time_limit_unlimited = 0 AND time_limit_from < %s AND time_limit_until > %s)',
			array('integer', 'integer'),
			array($now, $now));
		while ($row = $this->db->fetchAssoc($query))
		{
			$usr_id               = (int) $row['usr_id'];
			$this->users[$usr_id] = $usr_id;
		}
		$this->log->debug('Found '. count($this->users) .' users.');
	}

	/**
	 * Users with exceeded timings
	 */
	protected function gatherUsersWithExceededTimings()
	{
		$this->users_with_exceeded_timings = ilTimingsUser::lookupTimingsExceededByUser($this->users);
		$this->log->debug('Found '. count($this->users_with_exceeded_timings) .' users with exceeded timings.');
	}

	/**
	 * get new exceeded objects for users
	 */
	protected function getNewExceededObjectForUser()
	{
		$users_with_exceeded_objects = array();

		if(is_array($this->users_with_exceeded_timings) && count($this->users_with_exceeded_timings) > 0)
		{
			foreach($this->users_with_exceeded_timings as $key => $user_id)
			{
				$objects = $this->getExceededObjectsForUser($user_id);
				if(is_array($objects) && count($objects) > 0)
				{
					$obj_data = array();
					$already_notified = $this->getAlreadySentNotifications($user_id);
					$objects = array_diff_key($objects, $already_notified);
					foreach($objects as $ref_id => $v)
					{
						$detail_data = $this->getInformationForRefId($ref_id);
						$obj_data[$ref_id]				= $detail_data;
					}
					if(count($obj_data) > 0)
					{
						$users_with_exceeded_objects[$user_id] = $obj_data;
					}
				}
			}
			$this->log->debug('Found '. sizeof($users_with_exceeded_objects) .' users with new exceeded timings.');

			$this->buildExceededMails($users_with_exceeded_objects);
		}
	}

	/**
	 * Get freshly started objects
	 */
	protected function getFreshlyStartedObjectsForUser()
	{
		$users_with_new_started_object = array();

		if(is_array($this->users) && count($this->users) > 0)
		{
			foreach($this->users as $key => $user_id)
			{
				$objects = $this->getObjectsWithTimingsForUser($user_id);
				if(is_array($objects) && count($objects) > 0)
				{
					$obj_data = array();
					$already_notified = $this->getAlreadySentNotifications($user_id, false);
					$this->log->debug('User_id ' . $user_id .' was already notified for '. sizeof($already_notified) .' elements ');
					$objects = array_diff_key($objects, $already_notified);
					foreach($objects as $ref_id => $v)
					{
						$obj_data[$ref_id]	= $this->getInformationForRefId($ref_id);

						if(is_array($objects[$ref_id]))
						{
							if((isset($objects[$ref_id]['end']) && isset($objects[$ref_id]['start'])) && $objects[$ref_id]['end'] > $this->now)
							{
								if($objects[$ref_id]['start'] < $this->now)
								{
									$users_with_new_started_object[$user_id][$ref_id] = $obj_data[$ref_id];
								}
							}
							else
							{
								$this->log->debug('End is already older than today no notification send for user_id ' .$user_id .' on ref_id ' . $ref_id);
							}
						}
					}
				}
			}
			$this->log->debug('Found '. count($users_with_new_started_object) .' users with freshly started timings.');

			$this->buildFreshlyStartedMails($users_with_new_started_object);
		}
	}

	/**
	 * @param array $users_with_exceeded_objects
	 */
	protected function buildExceededMails($users_with_exceeded_objects)
	{
		$this->log->debug('Start.');
		if(is_array($users_with_exceeded_objects))
		{
			$this->log->debug('...found '. count($users_with_exceeded_objects));
			foreach($users_with_exceeded_objects as $user_id => $exceeded_objects)
			{
				$tpl = $this->buildTopMailBody($user_id, 'timings_cron_reminder_exceeded_start');
				$has_exceeded = $this->fillObjectListForMailBody($exceeded_objects, $tpl);

				if($has_exceeded)
				{
					$this->sendExceededMail($user_id, $exceeded_objects, $tpl->get());
					$this->log->debug('start sending exceeded mail to user: ' . $user_id);
				}
			}
		}
		else
		{
			$this->log->warning('no array given.');
		}

		$this->log->debug('end.');
	}

	/**
	 * @param array $users_with_freshly_started_objects
	 */
	protected function buildFreshlyStartedMails($users_with_freshly_started_objects)
	{
		$this->log->debug('start.');
		if(is_array($users_with_freshly_started_objects))
		{
			$this->log->debug('...found '. sizeof($users_with_freshly_started_objects));
			foreach($users_with_freshly_started_objects as $user_id => $freshly_started_objects)
			{
				$tpl = $this->buildTopMailBody($user_id, 'timings_cron_reminder_freshly_start');
				$has_freshly_started = $this->fillObjectListForMailBody($freshly_started_objects, $tpl);

				if($has_freshly_started)
				{
					$this->sendFreshlyStartedMail($user_id, $freshly_started_objects, $tpl->get());
				}
			}
		}
		else
		{
			$this->log->debug('no array given.');
		}

		$this->log->debug('end.');
	}

	/**
	 * @param $user_id
	 * @param $language_variable
	 * @return ilTemplate
	 */
	protected function buildTopMailBody($user_id, $language_variable)
	{
		$this->log->debug('start...');
		$tpl = new ilTemplate('tpl.crs_timings_cron_reminder_mail.html', true, true, 'Modules/Course');

		$this->getUserLanguage($user_id);
		$this->buildMailSalutation($user_id, $tpl);
		$tpl->setVariable('START_BODY', $this->user_lang->txt($language_variable));
		$this->log->debug('for user: ' . $user_id . ' end.');
		return $tpl;
	}

	/**
	 * @param $objects
	 * @param $tpl
	 * @return bool
	 */
	protected function fillObjectListForMailBody($objects, $tpl)
	{
		$has_elements = false;
		foreach($objects as $object_id => $object_details)
		{
			if($object_details['type'] == 'fold')
			{
				$tpl->setCurrentBlock('items');
				$tpl->setVariable('HREF',		$object_details['url']);
				$tpl->setVariable('ITEM_TITLE',	$object_details['title']);
				$tpl->parseCurrentBlock();
				$has_elements = true;
			}
		}

		$tpl->setVariable('INSTALLATION_SIGNATURE', \ilMail::_getInstallationSignature());

		$this->log->debug('found elements: ' . $has_elements);
		return $has_elements;
	}

	/**
	 * @param $user_id
	 */
	protected function getUserLanguage($user_id)
	{
		$this->log->debug('start...');
		$this->user_lang = ilLanguageFactory::_getLanguageOfUser($user_id);
		$this->user_lang->loadLanguageModule('crs');
		$this->user_lang->loadLanguageModule('mail');
		$this->log->debug('user language for user ' . $user_id . ' is ' . $this->user_lang->getLangKey() . ' end.');
	}

	/**
	 * @param $user_id
	 * @param ilTemplate $tpl
	 */
	protected function buildMailSalutation($user_id, $tpl)
	{
		$name		= ilObjUser::_lookupName($user_id);
		if(is_array($name))
		{
			$salutation	= $this->user_lang->txt('mail_salutation_n') . ' ';
			if($name['gender'] != '')
			{
				$salutation .= $this->user_lang->txt('salutation_' . $name['gender']) . ' ';
			}
			if($name['title'] != '')
			{
				$salutation .= $name['title'] . ' ';
			}
			$tpl->setVariable('SALUTATION', $salutation);
			$tpl->setVariable('FIRSTNAME', $name['firstname']);
			$tpl->setVariable('LASTNAME', $name['lastname']);
			$this->log->debug('Salutation: ' . $salutation . ' Firstname: ' .$name['firstname'] . ' Lastname: ' .$name['lastname']);
		}
		else
		{
			$this->log->debug('did not get an array from _lookupName.');
		}
	}

	/**
	 * @param $user_id
	 * @param $ref_ids
	 * @param $mail_body
	 */
	protected function sendExceededMail($user_id, $ref_ids, $mail_body)
	{
		$login = \ilObjUser::_lookupLogin($user_id);
		if ($login != '')
		{
			$mail = new ilMail(ANONYMOUS_USER_ID);
			if($this->hasUserActivatedNotification($user_id))
			{
				$mail->sendMail(
					$login, '', '',
					$this->user_lang->txt('timings_cron_reminder_exceeded_subject'),
					$mail_body,
					[],
					['normal'],
					true);
				$this->log->debug('...mail send for user '. $user_id .' to mail '. $login . ' has exceeded timings for ' . $mail_body);
				$this->markExceededInDatabase($user_id, $ref_ids);
			}
			else
			{
				$this->log->debug('... no mail was sent because user '. $user_id .' has deactivated their notifications and has no coaches assigned.');
			}
		}
		else
		{
			$this->log->debug('Not send. User ' . $user_id . ' has no email.');
		}
	}


	/**
	 * @param $user_id
	 * @param $ref_ids
	 * @param $mail_body
	 */
	protected function sendFreshlyStartedMail($user_id, $ref_ids, $mail_body)
	{
		$login = \ilObjUser::_lookupLogin($user_id);

		if ($login != '' && $this->hasUserActivatedNotification($user_id))
		{
			$mail = new ilMail(ANONYMOUS_USER_ID);
			$mail->sendMail(
				$login, '', '',
				$this->user_lang->txt('timings_cron_reminder_started_subject'),
				$mail_body,
				[],
				['normal'],
				true
			);
			$this->log->debug('...mail send for user '. $user_id .' to mail '. $login . ' has freshly started timings for ' . $mail_body);
			$this->markFreshlyStartedInDatabase($user_id, $ref_ids);
		}
		else
		{
			$this->log->debug('Not send. User '. $user_id .' has no email.');
		}
	}

	/**
	 * @param int $user_id
	 * @param array $ref_ids
	 */
	protected function markExceededInDatabase($user_id, $ref_ids)
	{
		foreach($ref_ids as $ref_id => $data)
		{
			$this->db->manipulateF('INSERT INTO '.ilCourseConstants::CRON_TIMINGS_EXCEEDED_TABLE.' (user_id, ref_id, sent) VALUES '.
				' (%s,%s,%s)',
				array('integer', 'integer', 'integer'),
				array($user_id, $ref_id, $this->now));

			$this->log->debug('ilTimingsCronReminder->markExceededInDatabase: Marked exceeded in Database. User '.$user_id .' ref_id ' . $ref_id );
		}
	}

	/**
	 * @param int $user_id
	 * @param array $ref_ids
	 */
	protected function markFreshlyStartedInDatabase($user_id, $ref_ids)
	{
		foreach($ref_ids as $ref_id => $data)
		{
			$this->db->manipulateF('INSERT INTO '.ilCourseConstants::CRON_TIMINGS_STARTED_TABLE.' (user_id, ref_id, sent) VALUES '.
				' (%s,%s,%s)',
				array('integer', 'integer', 'integer'),
				array($user_id, $ref_id, $this->now));

			$this->log->debug('ilTimingsCronReminder->markFreshlyStartedInDatabase: Marked freshly started in Database. User '.$user_id .' ref_id ' . $ref_id );
		}
	}

	/**
	 * @param int $user_id
	 * @param bool|true $for_exceeded
	 * @return array
	 */
	protected function getAlreadySentNotifications($user_id, $for_exceeded = true)
	{
		$ref_ids	= array();
		$table		= ilCourseConstants::CRON_TIMINGS_EXCEEDED_TABLE;

		if( ! $for_exceeded)
		{
			$table =  ilCourseConstants::CRON_TIMINGS_STARTED_TABLE;
		}

		$result = $this->db->queryF('SELECT * FROM '. $table .' WHERE '.
			'user_id = %s',
			array('integer'),
			array($user_id));

		while ($record = $this->db->fetchAssoc($result))
		{
			$ref_ids[$record['ref_id']] = $record['ref_id'];
		}

		return $ref_ids;
	}

	/**
	 * @param $ref_id
	 * @return mixed
	 */
	protected function getInformationForRefId($ref_id)
	{
		if(!array_key_exists($ref_id, self::$objects_information))
		{
			$obj_id = $this->obj_data_cache->lookupObjId($ref_id);
			$type 	= $this->obj_data_cache->lookupType($obj_id);
			$value = array(	'title'	=> $this->obj_data_cache->lookupTitle($obj_id),
							'type'	=> $type,
							'url'	=> ilLink::_getLink($ref_id, $type),
							'obj_id'=> $obj_id
			);
			self::$objects_information[$ref_id] = $value;

			$this->log->debug('ilTimingsCronReminder->getInformationForRefId: ...cached object information for => '. $value['type'] . ' => ' . $value['title']);
		}
		return self::$objects_information[$ref_id];
	}


	/**
	 * @param $user_id
	 * @return array
	 */
	protected function getExceededObjectsForUser($user_id)
	{
		$exceeded_obj_list = ilTimingsUser::lookupTimings(array($user_id), $arr = array(), true, true);
		return $exceeded_obj_list;
	}

	/**
	 * @param $user_id
	 * @return array
	 */
	protected function getObjectsWithTimingsForUser($user_id)
	{
		$meta = array();
		$timings_obj_list = ilTimingsUser::lookupTimings(array($user_id), $meta, false, true);
		$meta = $meta[$user_id];
		return $meta;
	}
	
	protected function hasUserActivatedNotification($user_id)
	{
		return true;
	}

}

?>