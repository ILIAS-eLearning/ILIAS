<?php


class ilCertificateQueueRepository
{
	/**
	 * @var ilDB
	 */
	private $database;

	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @param ilDBInterface $database
	 * @param ilLogger $logger
	 */
	public function __construct(\ilDBInterface $database, ilLogger $logger)
	{
		$this->database = $database;
		$this->logger = $logger;
	}

	/**
	 * @param ilCertificateQueueEntry $certificateQueueEntry
	 */
	public function addToQueue(ilCertificateQueueEntry $certificateQueueEntry)
	{
		$this->logger->info('START - Add new entry to certificate cron job queue');

		$id = $this->database->nextId('certificate_cron_queue');

		$row = array(
			'id'                => array('integer', $id),
			'obj_id'            => array('integer', $certificateQueueEntry->getObjId()),
			'usr_id'            => array('integer', $certificateQueueEntry->getUserId()),
			'adapter_class'     => array('clob', $certificateQueueEntry->getAdapterClass()),
			'state'             => array('clob', $certificateQueueEntry->getState()),
			'started_timestamp' => array('integer', $certificateQueueEntry->getStartedTimestamp())
		);

		$this->logger->debug(sprintf('Save queue entry with following values: %s', json_encode($row, JSON_PRETTY_PRINT)));
		$this->logger->info(sprintf('END - Added entry to queue'));

		$this->database->insert('certificate_cron_queue', $row);
	}

	/**
	 * @param integer $id
	 * @throws ilDatabaseException
	 */
	public function removeFromQueue($id)
	{
		$this->logger->info(sprintf('START - Remove entry(id: "%s") from queue', $id));

		$sql = 'DELETE FROM certificate_cron_queue WHERE id = ' . $this->database->quote($id, 'integer');

		$this->database->manipulate($sql);

		$this->logger->info(sprintf('END - Entry(id: "%s") deleted from queue', $id));
	}

	/**
	 * @return array
	 */
	public function getAllEntriesFromQueue()
	{
		$this->logger->info('START - Fetch all entries from queue');

		$sql = 'SELECT * FROM certificate_cron_queue';
		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$this->logger->debug(sprintf('Queue entry found: ', json_encode($row, JSON_PRETTY_PRINT)));

			$result[] = new ilCertificateQueueEntry(
				$row['obj_id'],
				$row['usr_id'],
				$row['adapter_class'],
				$row['state'],
				$row['started_timestamp'],
				$row['id']
			);
		}

		$this->logger->info(sprintf('END - All queue entries fetched(Total: "%s")', count($result)));

		return $result;
	}
}
