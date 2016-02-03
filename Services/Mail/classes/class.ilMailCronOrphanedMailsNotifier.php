<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailNotifier
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotifier 
{
	/**
	 * @var ilMailCronOrphanedMailsNotificationCollector|null
	 */
	protected $collector = NULL;

	/**
	 * @param ilMailCronOrphanedMailsNotificationCollector $collector
	 */
	public function __construct(ilMailCronOrphanedMailsNotificationCollector $collector)
	{
		$this->collector = $collector;
	}

	/**
	 * @param ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj
	 */
	private function markAsNotified(ilMailCronOrphanedMailsNotificationCollectionObj$collection_obj)
	{
		global $ilDB;

		if($this->threshold > $this->mail_notify_orphaned )
		{
			$notify_days_before = $this->threshold - $this->mail_notify_orphaned;
		}
		else
		{
			$notify_days_before = 1;
		}

		$ts_delete = strtotime("+ ".$notify_days_before." days");
		$this->ts_for_deletion = mktime(0, 0, 0, date('m', $ts_delete), date('d', $ts_delete), date('Y', $ts_delete));

		foreach($collection_obj->getFolderObjects() as $folder_obj)
		{
			$folder_id = $folder_obj->getFolderId();
			
			foreach($folder_obj->getOrphanedMailObjects() as $mail_obj)
			{
				$mail_id = $mail_obj->getMailId();
			
					$ilDB->insert('mail_cron_orphaned', array(
						'mail_id' 		=> array('integer', $mail_id),
						'folder_id'		=> array('integer', $folder_id),
						'ts_do_delete'	=> array('integer', $this->ts_for_deletion))
				);
			}
		}	
	}

	/**
	 * @param ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj
	 */
	private function sendMail(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj)
	{
		include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsNotification.php';
		$mail = new ilMailCronOrphanedMailsNotification();

		$mail->setRecipients(array($collection_obj->getUserId()));
		$mail->setAdditionalInformation(array('mail_folders' => $collection_obj->getFolderObjects()));
		$mail->send();
	}

	/**
	 * 
	 */
	public function processNotification()
	{
		foreach($this->collector->getCollection() as $collection_obj)
		{
			$this->sendMail($collection_obj);
			$this->markAsNotified($collection_obj);
		}	
	}
}