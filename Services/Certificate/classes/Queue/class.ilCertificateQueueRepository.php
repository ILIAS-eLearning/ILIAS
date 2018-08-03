<?php


class ilCertificateQueueRepository
{
	/**
	 * @var ilDB
	 */
	private $database;

	/**
	 * @param ilDB $database
	 */
	public function __construct(\ilDBInterface $database)
	{
		$this->database = $database;
	}

	/**
	 * @param ilCertificateQueueEntry $certificateQueueEntry
	 */
	public function addToQueue(ilCertificateQueueEntry $certificateQueueEntry)
	{
		$id = $this->database->nextId('certificate_cron_queue');

		$this->database->insert('certificate_cron_queue', array(
			'id'                => array('integer', $id),
			'obj_id'            => array('integer', $certificateQueueEntry->getObjId()),
			'usr_id'            => array('integer', $certificateQueueEntry->getUserId()),
			'adapter_class'     => array('clob', $certificateQueueEntry->getAdapterClass()),
			'state'             => array('clob', $certificateQueueEntry->getState()),
			'started_timestamp' => array('integer', $certificateQueueEntry->getStartedTimestamp())
		));
	}

	/**
	 * @param integer $id
	 * @throws ilDatabaseException
	 */
	public function removeFromQueue($id)
	{
		$sql = 'DELETE FROM certificate_cron_queue WHERE id = ' . $this->database->quote($id, 'integer');

		$query = $this->database->query($sql);

		$this->database->execute($query);
	}

	/**
	 * @return array
	 */
	public function getAllEntriesFromQueue()
	{
		$sql = 'SELECT * FROM certificate_cron_queue';
		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$result[] = new ilCertificateQueueEntry(
				$row['obj_id'],
				$row['usr_id'],
				$row['adapter_class'],
				$row['state'],
				$row['started_timestamp'],
				$row['id']
			);
		}

		return $result;
	}
}
