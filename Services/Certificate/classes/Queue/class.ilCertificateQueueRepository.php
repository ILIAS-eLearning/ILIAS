<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateQueueRepository
{
    public function __construct(private ilDBInterface $database, private ilLogger $logger)
    {
    }

    public function addToQueue(ilCertificateQueueEntry $certificateQueueEntry): void
    {
        $this->logger->debug('START - Add new entry to certificate cron job queue');

        $id = $this->database->nextId('il_cert_cron_queue');

        $row = [
            'id' => ['integer', $id],
            'obj_id' => ['integer', $certificateQueueEntry->getObjId()],
            'usr_id' => ['integer', $certificateQueueEntry->getUserId()],
            'adapter_class' => ['text', $certificateQueueEntry->getAdapterClass()],
            'state' => ['text', $certificateQueueEntry->getState()],
            'started_timestamp' => ['integer', $certificateQueueEntry->getStartedTimestamp()],
            'template_id' => ['integer', $certificateQueueEntry->getTemplateId()],
        ];

        $this->logger->debug(sprintf(
            'Save queue entry with following values: %s',
            json_encode($row, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        ));
        $this->logger->debug('END - Added entry to queue');

        $this->database->insert('il_cert_cron_queue', $row);
    }

    public function removeFromQueue(int $id): void
    {
        $this->logger->debug(sprintf('START - Remove entry(id: "%s") from queue', $id));

        $sql = 'DELETE FROM il_cert_cron_queue WHERE id = ' . $this->database->quote($id, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Entry(id: "%s") deleted from queue', $id));
    }

    /**
     * @return ilCertificateQueueEntry[]
     */
    public function getAllEntriesFromQueue(): array
    {
        $this->logger->debug('START - Fetch all entries from queue');

        $sql = 'SELECT * FROM il_cert_cron_queue';
        $query = $this->database->query($sql);

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf(
                'Queue entry found: "%s"',
                json_encode($row, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
            ));

            $result[] = new ilCertificateQueueEntry(
                (int) $row['obj_id'],
                (int) $row['usr_id'],
                $row['adapter_class'],
                $row['state'],
                (int) $row['template_id'],
                (int) $row['started_timestamp'],
                (int) $row['id']
            );
        }

        $this->logger->debug(sprintf('END - All queue entries fetched(Total: "%s")', count($result)));

        return $result;
    }

    public function removeFromQueueByUserId(int $user_id): void
    {
        $this->logger->debug(sprintf('START - Remove entries for user (usr_id: "%s") from queue', $user_id));

        $sql = 'DELETE FROM il_cert_cron_queue WHERE usr_id = ' . $this->database->quote($user_id, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Entries for user (usr_id: "%s") deleted from queue', $user_id));
    }
}
