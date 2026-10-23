<?php

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

declare(strict_types=1);

namespace ILIAS\Mail\Folder;

use Generator;
use DateTimeZone;
use ilDBConstants;
use ilDBInterface;
use MailDeliveryData;
use DateTimeImmutable;
use ILIAS\Data\Clock\ClockFactory;
use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\Mail\Message\MailRecordMapper;

readonly class OutboxDatabaseRepository implements OutboxRepository
{
    public function __construct(
        private ilDBInterface $db,
        private ClockFactory $clock,
        private MailRecordMapper $mail_record_mapper,
    ) {
    }

    /**
     * @return Generator<MailDeliveryData>
     */
    public function getOutboxMails(): Generator
    {
        $res = $this->db->queryF(
            <<<'SQL'
            SELECT 
                mail.mail_id,
                mail.user_id,
                mail.folder_id,
                rcp_to, 
                rcp_cc, 
                rcp_bcc, 
                m_subject,
                m_message, 
                attachments, 
                use_placeholders, 
                schedule_datetime, 
                schedule_timezone
            FROM mail 
            INNER JOIN mail_obj_data ON mail.folder_id = mail_obj_data.obj_id AND mail.user_id = mail_obj_data.user_id
            INNER JOIN usr_data ON usr_data.usr_id = mail.user_id
                 WHERE mail_obj_data.m_type = %s 
                   AND schedule_datetime IS NOT NULL
            SQL,
            [ilDBConstants::T_TEXT],
            [MailFolderType::OUTBOX->value]
        );
        $current_time = $this->clock->utc()->now();

        while ($row = $this->db->fetchAssoc($res)) {
            if (!is_array($row)) {
                continue;
            }

            $schedule_datetime = new DateTimeImmutable(
                (string) $row['schedule_datetime'],
                new DateTimeZone((string) $row['schedule_timezone'])
            );
            if ($schedule_datetime > $current_time) {
                continue;
            }

            $record = $this->mail_record_mapper->fromRow($row);
            if ($record === null) {
                continue;
            }

            yield new MailDeliveryData(
                $record->getRcpTo() ?? '',
                $record->getRcpCc() ?? '',
                $record->getRcpBc() ?? '',
                $record->getSubject() ?? '',
                $record->getMessage() ?? '',
                $record->getAttachments() ?? MailAttachments::empty(),
                (bool) $record->getUsePlaceholders(),
                $record->getMailId(),
                $record->getUserId()
            );
        }
    }

    public function markAsDelivered(int $owner_id, int $mail_id): void
    {
        $this->db->manipulateF(
            'UPDATE mail SET schedule_datetime = NULL, schedule_timezone = NULL WHERE mail_id = %s AND user_id = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$mail_id, $owner_id]
        );
    }
}
