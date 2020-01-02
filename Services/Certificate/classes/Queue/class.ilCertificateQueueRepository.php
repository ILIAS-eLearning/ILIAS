<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
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

        $id = $this->database->nextId('il_cert_cron_queue');

        $row = array(
            'id'                => array('integer', $id),
            'obj_id'            => array('integer', $certificateQueueEntry->getObjId()),
            'usr_id'            => array('integer', $certificateQueueEntry->getUserId()),
            'adapter_class'     => array('text', $certificateQueueEntry->getAdapterClass()),
            'state'             => array('text', $certificateQueueEntry->getState()),
            'started_timestamp' => array('integer', $certificateQueueEntry->getStartedTimestamp()),
            'template_id'       => array('integer', $certificateQueueEntry->getTemplateId()),

        );

        $this->logger->debug(sprintf('Save queue entry with following values: %s', json_encode($row, JSON_PRETTY_PRINT)));
        $this->logger->info(sprintf('END - Added entry to queue'));

        $this->database->insert('il_cert_cron_queue', $row);
    }

    /**
     * @param integer $id
     * @throws ilDatabaseException
     */
    public function removeFromQueue($id)
    {
        $this->logger->info(sprintf('START - Remove entry(id: "%s") from queue', $id));

        $sql = 'DELETE FROM il_cert_cron_queue WHERE id = ' . $this->database->quote($id, 'integer');

        $this->database->manipulate($sql);

        $this->logger->info(sprintf('END - Entry(id: "%s") deleted from queue', $id));
    }

    /**
     * @return array
     */
    public function getAllEntriesFromQueue()
    {
        $this->logger->info('START - Fetch all entries from queue');

        $sql = 'SELECT * FROM il_cert_cron_queue';
        $query = $this->database->query($sql);

        $result = array();
        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Queue entry found: ', json_encode($row, JSON_PRETTY_PRINT)));

            $result[] = new ilCertificateQueueEntry(
                $row['obj_id'],
                $row['usr_id'],
                $row['adapter_class'],
                $row['state'],
                $row['template_id'],
                $row['started_timestamp'],
                $row['id']
            );
        }

        $this->logger->info(sprintf('END - All queue entries fetched(Total: "%s")', count($result)));

        return $result;
    }

    /**
     * @param int $user_id
     */
    public function removeFromQueueByUserId(int $user_id)
    {
        $this->logger->info(sprintf('START - Remove entries for user(user_id: "%s") from queue', $user_id));

        $sql = 'DELETE FROM il_cert_cron_queue WHERE usr_id = ' . $this->database->quote($user_id, 'integer');

        $this->database->manipulate($sql);

        $this->logger->info(sprintf('END - Entries for user(user_id: "%s") deleted from queue', $user_id));
    }
}
