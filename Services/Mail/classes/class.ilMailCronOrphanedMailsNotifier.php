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
    private int $threshold = 0;
    private int $mail_notify_orphaned = 0;
    private ilDBStatement $mark_as_notified_stmt;

    public function __construct(
        ilMailCronOrphanedMails $job,
        ilMailCronOrphanedMailsNotificationCollector $collector,
        int $threshold,
        int $mail_notify_orphaned
    ) {
        global $DIC;

        $this->db = $DIC->database();

        $this->job = $job;
        $this->collector = $collector;
        $this->threshold = $threshold;
        $this->mail_notify_orphaned = $mail_notify_orphaned;

        $this->mark_as_notified_stmt = $this->db->prepare(
            'INSERT INTO mail_cron_orphaned (mail_id, folder_id, ts_do_delete) VALUES (?, ?, ?)',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER]
        );
    }

    private function markAsNotified(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj) : void
    {
        $notify_days_before = 1;
        if ($this->threshold > $this->mail_notify_orphaned) {
            $notify_days_before = $this->threshold - $this->mail_notify_orphaned;
        }

        $ts_delete = strtotime("+ " . $notify_days_before . " days");
        $ts_for_deletion = mktime(
            0,
            0,
            0,
            (int) date('m', $ts_delete),
            (int) date('d', $ts_delete),
            (int) date('Y', $ts_delete)
        );

        $i = 0;
        foreach ($collection_obj->getFolderObjects() as $folder_obj) {
            $folder_id = $folder_obj->getFolderId();

            foreach ($folder_obj->getOrphanedMailObjects() as $mail_obj) {
                $mail_id = $mail_obj->getMailId();

                if ($i % self::NOTIFICATION_MARKER_PING_THRESHOLD === 0) {
                    $this->job->ping();
                }

                $this->db->execute(
                    $this->mark_as_notified_stmt,
                    [$mail_id, $folder_id, $ts_for_deletion]
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
            if ($i % self::MAIL_DELIVERY_PING_THRESHOLD === 0) {
                $this->job->ping();
            }

            $this->sendMail($collection_obj);
            $this->markAsNotified($collection_obj);
            ++$i;
        }
    }
}
