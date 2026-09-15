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

use ilMail;
use Generator;
use DateTimeZone;
use ilDBConstants;
use ilDBInterface;
use MailDeliveryData;
use DateTimeImmutable;
use ILIAS\Data\Clock\ClockFactory;

readonly class OutboxDatabaseRepository implements OutboxRepository
{
    public function __construct(
        private ilDBInterface $db,
        private ClockFactory $clock,
        private ilMail $mail,
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
                mail_id,
                mail.user_id,
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
                 WHERE mail_obj_data.m_type = %s 
                   AND schedule_datetime IS NOT NULL
            SQL,
            [ilDBConstants::T_TEXT],
            [MailFolderType::OUTBOX->value]
        );
        $current_time = $this->clock->utc()->now();

        while ($row = $this->mail->fetchMailData($this->db->fetchAssoc($res))) {
            $schedule_datetime = new DateTimeImmutable(
                $row['schedule_datetime'],
                new DateTimeZone($row['schedule_timezone'])
            );
            if ($schedule_datetime <= $current_time) {
                yield new MailDeliveryData(
                    $row['rcp_to'],
                    $row['rcp_cc'],
                    $row['rcp_bcc'],
                    $row['m_subject'],
                    $row['m_message'],
                    $row['attachments'],
                    (bool) ($row['use_placeholders'] ?? false),
                    (int) $row['mail_id'],
                    (int) $row['user_id']
                );
            }
        }
    }

    public function deleteOutboxMail(int $user_id, int $mail_id): void
    {
        (new ilMail($user_id))->deleteMails([$mail_id]);
    }

    public function deleteOrphanScheduledMail(int $mail_id): void
    {
        $res = $this->db->queryF(
            'SELECT user_id FROM mail WHERE mail_id = %s',
            [ilDBConstants::T_INTEGER],
            [$mail_id]
        );
        $row = $this->db->fetchAssoc($res);
        if (!is_array($row)) {
            return;
        }

        $user_id = (int) ($row['user_id'] ?? 0);
        if ($user_id <= 0) {
            return;
        }

        (new ilMail($user_id))->deleteMails([$mail_id]);
    }
}
