<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsNotificationCollectionObj.php';
include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsFolderObject.php';
include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsFolderMailObject.php';

/**
 * ilMailCronOrphanedMailsNotificationCollector
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotificationCollector
{
	/**
	 * @var array ilMailCronOrphanedMailsNotificationCollectionObj
	 */
	protected $collection = array();

	/**
	 * 
	 */
	public function __construct()
	{
		$this->collect();
	}

	/**
	 * 
	 */
	public function collect()
	{
		global $ilDB, $ilSetting;

		$mail_notify_orphaned = (int)$ilSetting->get('mail_notify_orphaned');
		$mail_threshold = (int)$ilSetting->get('mail_threshold');
		
		if($mail_threshold > $mail_notify_orphaned )
		{
			$notify_days_before = $mail_threshold - $mail_notify_orphaned;
		}
		else
		{
			$notify_days_before = 1;
		}

		$ts_notify = strtotime("- ".$notify_days_before." days");
		$ts_for_notification = date('Y-m-d', $ts_notify).' 23:59:59';

		$res = $ilDB->query('SELECT mail_id FROM mail_cron_orphaned');
		$already_notified = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$already_notified[] = $row['mail_id'];
		}

		//.. überprüfen ob es bereits einen Eintrag in "mail_cron_orphaned" gibt
		$types = array('timestamp');
		$data  = array($ts_for_notification);

		$notification_query = "
				SELECT 		mail_id, m.user_id, folder_id, send_time, m_subject, mdata.title
				FROM 		mail m
				INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
				WHERE 		send_time <= %s";

		if((int)$ilSetting->get('mail_only_inbox_trash') > 0)
		{
			$notification_query .= " AND (mdata.m_type = %s OR mdata.m_type = %s)";
			$types = array('timestamp', 'text', 'text');
			$data  = array($ts_for_notification, 'inbox', 'trash');
		}

		$notification_query .= " AND " . $ilDB->in('mail_id', $already_notified, true, 'integer')
			. " ORDER BY m.user_id, folder_id, mail_id";

		$res = $ilDB->queryF($notification_query, $types, $data);

		$collection_obj = NULL;
		$folder_obj = NULL;

		while($row = $ilDB->fetchAssoc($res))
		{
			if(!$this->existsCollectionObjForUserId($row['user_id']))
			{
				if(is_object($collection_obj))
				{
					$this->addCollectionObject($collection_obj);
					unset($collection_obj);
				}	
			}
			
			if(!is_object($collection_obj))
			{
				$collection_obj = new ilMailCronOrphanedMailsNotificationCollectionObj($row['user_id']);
			}	
			
			if(is_object($collection_obj))
			{
				if(!$folder_obj = $collection_obj->getFolderObjectById($row['folder_id']))
				{
					$folder_obj = new ilMailCronOrphanedMailsFolderObject($row['folder_id']);
					$folder_obj->setFolderTitle($row['title']);
					$collection_obj->addFolderObject($folder_obj);
				}
				
				if(is_object($folder_obj))
				{
					$orphaned_mail_obj = new ilMailCronOrphanedMailsFolderMailObject($row['mail_id'], $row['m_subject']);
					$folder_obj->addMailObject($orphaned_mail_obj);
				}
			}
		}
		if(is_object($collection_obj))
		{
			$this->addCollectionObject($collection_obj);
			unset($collection_obj);
		}
	}

	/**
	 * @param ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj
	 */
	public function addCollectionObject(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj)
	{
		$this->collection[$collection_obj->getUserId()] = $collection_obj;
	}

	/**
	 * @param $user_id
	 * @return bool
	 */
	private function existsCollectionObjForUserId($user_id)
	{
		if(isset($this->collection[$user_id]))
		{
			return true;
		}	 
		return false;	 
	}

	/**
	 * @return array ilMailCronOrphanedMailsNotificationCollectionObj
	 */
	public function getCollection()
	{
		return $this->collection;
	}	
}