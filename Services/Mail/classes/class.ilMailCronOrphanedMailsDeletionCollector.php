<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsDeletionCollector
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailCronOrphanedMailsDeletionCollector
{
	/**
	 * @var array
	 */
	protected $mail_ids = array();

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

		$mail_only_inbox_trash = (int)$ilSetting->get('mail_only_inbox_trash');
		$last_cron_start_ts = (int)$ilSetting->get('last_cronjob_start_ts');
		
		$now = time();
		if($last_cron_start_ts != NULL)
		{
			if($mail_only_inbox_trash)
			{
				// überprüfen ob die mail in einen anderen Ordner verschoben wurde
				// selektiere die, die tatsächlich gelöscht werden sollen				
				$res = $ilDB->queryF("
						SELECT * FROM mail_cron_orphaned 
						INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
						WHERE ts_do_delete <= %s
						AND (mdata.m_type = %s OR mdata.m_type = %s)",
					array('integer', 'text', 'text'),
					array($now, 'inbox', 'trash'));
			}
			else
			{
				// selektiere alle zu löschenden mails unabhängig vom ordner.. 
				$res = $ilDB->queryF("
					SELECT * FROM mail_cron_orphaned 
					WHERE ts_do_delete <= %s",
					array('integer'),
					array($now));
			}

			while($row = $ilDB->fetchAssoc($res))
			{
				$this->addMailIdToDelete($row['mail_id']);
			}
		}
	}

	/**
	 * @param int $mail_id
	 */
	public function addMailIdToDelete($mail_id)
	{
		$this->mail_ids[] = (int)$mail_id;
	}

	/**
	 * @return array
	 */
	public function getMailIdsToDelete()
	{
		return $this->mail_ids;
	}	 
}