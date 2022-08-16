<?php declare(strict_types=1);

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

use ILIAS\Data\Clock\ClockInterface;
use ILIAS\Data\Factory;

/**
 * ilMailCronOrphanedMailNotifier
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotifier
{
    private const NOTIFICATION_MARKER_PING_THRESHOLD = 250;
    private const MAIL_DELIVERY_PING_THRESHOLD = 25;

    private ilMailCronOrphanedMails $job;
    private ilMailCronOrphanedMailsNotificationCollector $collector;
    private ilDBInterface $db;
    private ClockInterface $clock;
    private int $mail_expiration_days;
    private int $mail_expiration_warning_days;
    private ilDBStatement $mark_as_notified_stmt;

    public function __construct(
        ilMailCronOrphanedMails $job,
        ilMailCronOrphanedMailsNotificationCollector $collector,
        int $mail_expiration_days,
        int $mail_expiration_warning_days,
        ?ilDBInterface $db = null,
        ?ClockInterface $clock = null
    ) {
        global $DIC;

        $this->db = $db ?? $DIC->database();
        $this->clock = $clock ?? (new Factory())->clock()->system();

        $this->job = $job;
        $this->collector = $collector;
        $this->mail_expiration_days = $mail_expiration_days;
        $this->mail_expiration_warning_days = $mail_expiration_warning_days;

        $this->mark_as_notified_stmt = $this->db->prepareManip(
            'INSERT INTO mail_cron_orphaned (mail_id, folder_id, ts_do_delete) VALUES (?, ?, ?)',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER]
        );
    }

    private function markAsNotified(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj) : void
    {
        $notify_days_before = 1;
        if ($this->mail_expiration_days > $this->mail_expiration_warning_days) {
            $notify_days_before = $this->mail_expiration_days - $this->mail_expiration_warning_days;
        }

        $deletion_datetime = $this->clock->now()
            ->modify('+ ' . $notify_days_before . ' days')
            ->setTime(0, 0);

        $i = 0;
        foreach ($collection_obj->getFolderObjects() as $folder_obj) {
            $folder_id = $folder_obj->getFolderId();

            foreach ($folder_obj->getOrphanedMailObjects() as $mail_obj) {
                $mail_id = $mail_obj->getMailId();

                if ($i > 0 && $i % self::NOTIFICATION_MARKER_PING_THRESHOLD === 0) {
                    $this->job->ping();
                }

                $this->db->execute(
                    $this->mark_as_notified_stmt,
                    [$mail_id, $folder_id, $deletion_datetime->getTimestamp()]
                );
                $i++;
            }
        }
    }

    private function sendMail(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj) : void
    {
        $mail = new ilMailCronOrphanedMailsNotification();

        $mail->setRecipients([$collection_obj->getUserId()]);
        $mail->setAdditionalInformation(['mail_folders' => $collection_obj->getFolderObjects()]);
        $mail->send();
    }

    public function processNotification() : void
    {
        $i = 0;
        foreach ($this->collector->getCollection() as $collection_obj) {
            if ($i > 0 && $i % self::MAIL_DELIVERY_PING_THRESHOLD === 0) {
                $this->job->ping();
            }

            $this->sendMail($collection_obj);
            $this->markAsNotified($collection_obj);
            ++$i;
        }
    }
}
