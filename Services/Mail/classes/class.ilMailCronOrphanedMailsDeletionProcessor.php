<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsDeletionProcessor
 * @author Nadia Ahmad <nahmad@databay.de> 
 */
class ilMailCronOrphanedMailsDeletionProcessor
{
	/**
	 * @var ilMailCronOrphanedMailsDeletionCollector
	 */
	protected $collector;

	/**
	 * @param ilMailCronOrphanedMailsDeletionCollector $collector
	 */
	public function __construct(ilMailCronOrphanedMailsDeletionCollector $collector)
	{
		$this->collector = $collector;
	}
	
	/**
	 * @param array $attachment_ids
	 */
	private function deleteAttachments()
	{
		global $ilDB, $ilLog;

		$attachment_paths = array();

		// @todo TESTEN!!!: Anhänge dürfen nur dann gelöscht werden, wenn deren Pfaf in Table mail_attachment bei keinen anderen Einträgen mehr benutzt wird

		$res = $ilDB->query('
				SELECT path, COUNT(mail_id) cnt_mail_ids
				FROM mail_attachment 
				WHERE '. $ilDB->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer').'
				GROUP BY path');
		
		while($row = $ilDB->fetchAssoc($res))
		{
			$usage_res = $ilDB->queryF('SELECT mail_id, path FROM mail_attachment WHERE path = %s', 
				array('text'), array($row['path']));
			
			$num_rows = $ilDB->numRows($usage_res);
			
			if($row['cnt_mail_ids'] >= $num_rows)
			{
				// collect path to delete attachment file
				$attachment_paths[$row['mail_id']] = $row['path'];
			}
		}

		foreach($attachment_paths as $mail_id => $path)
		{
			try
			{
				$path = CLIENT_DATA_DIR.'/mail/'.$path;
				$iter = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

				foreach($iter as $file)
				{
					/**
					 * @var $file SplFileInfo
					 */

					if($file->isDir())
					{
						@rmdir($file->getPathname());
					}
					else
					{
						@unlink($file->getPathname());

						$ilLog->write(__METHOD__ . ': Attachment ('.$path.') deleted for mail_id: ' . $mail_id);
					}
				}
				@rmdir($path);
			}
			catch(Exception $e) { }
		}

		$ilDB->manipulate('DELETE FROM mail_attachment WHERE '. $ilDB->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer'));
	}

	/**
	 * @param array $mail_ids
	 */
	private function deleteMails()
	{
		global $ilDB;
		
		$ilDB->manipulate('DELETE FROM mail WHERE ' . $ilDB->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer'));
	}

	/**
	 * @param $mail_ids
	 */
	private function deleteMarkedAsNotified()
	{
		global $ilDB;
		
		$ilDB->manipulate('DELETE FROM mail_cron_orphaned WHERE ' . $ilDB->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer'));
	}
	
	/**
	 *
	 */
	public function processDeletion()
	{	
		global $ilLog;
	
		if(count($this->collector->getMailIdsToDelete()) > 0)
		{
			// delete possible attachments ... 
			$this->deleteAttachments();
			

			$this->deleteMails();
			$ilLog->write(__METHOD__ . ': Deleted mail_ids: ' . implode(', ', $this->collector->getMailIdsToDelete()));

			$this->deleteMarkedAsNotified();
			$ilLog->write(__METHOD__ . ': Deleted mail_cron_orphaned mail_ids: ' . implode(', ', $this->collector->getMailIdsToDelete()));
		}
	}
}