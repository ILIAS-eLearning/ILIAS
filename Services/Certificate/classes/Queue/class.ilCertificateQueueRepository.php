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
	public function __construct(\ilDB $database)
	{
		$this->database = $database;
	}

	public function addToQueue(ilCertificateQueueEntry $certificateQueueEntry)
	{
		$this->database->insert('certificate_cron_queue', array(
			'obj_id' => $certificateQueueEntry->getObjId(),
			'usr_id' => $certificateQueueEntry->getUserId(),
			'adapter_class' => $certificateQueueEntry->getAdapterClass(),
			'state' => $certificateQueueEntry->getState(),
			'started_timestamp' => timestamp()
		));
	}

	public function removeFromQueue($id)
	{
		$sql = 'DELETE FROM certificate_cron_queue WHERE id = ' . $this->database->quote($id, 'integer');
		$this->database->execute($sql);
	}
}
