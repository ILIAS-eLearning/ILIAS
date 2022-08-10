<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailNotifier
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotifier
{
    private const NOTIFICATION_MARKER_PING_THRESHOLD = 250;
    private const MAIL_DELIVERY_PING_THRESHOLD = 25;

    private $job;
    /**
     * @var ilMailCronOrphanedMailsNotificationCollector
     */
    protected $collector;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var int
     */
    protected $threshold = 0;

    /**
     * @var int
     */
    protected $mail_notify_orphaned = 0;
    private $mark_as_notified_stmt;

    /**
     * ilMailCronOrphanedMailsNotifier constructor.
     * @param ilMailCronOrphanedMailsNotificationCollector $collector
     * @param int                                          $threshold
     * @param int                                          $mail_notify_orphaned
     */
    public function __construct(ilMailCronOrphanedMails $job, ilMailCronOrphanedMailsNotificationCollector $collector, $threshold, $mail_notify_orphaned)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->job = $job;
        $this->collector = $collector;
        $this->threshold = $threshold;
        $this->mail_notify_orphaned = $mail_notify_orphaned;

        $this->mark_as_notified_stmt = $this->db->prepareManip(
            'INSERT INTO mail_cron_orphaned (mail_id, folder_id, ts_do_delete) VALUES (?, ?, ?)',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER]
        );
    }

    /**
     * @param ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj
     */
    private function markAsNotified(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj)
    {
        if ($this->threshold > $this->mail_notify_orphaned) {
            $notify_days_before = $this->threshold - $this->mail_notify_orphaned;
        } else {
            $notify_days_before = 1;
        }

        $ts_delete = strtotime("+ " . $notify_days_before . " days");
        $ts_for_deletion = mktime(0, 0, 0, date('m', $ts_delete), date('d', $ts_delete), date('Y', $ts_delete));

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
                    [$mail_id, $folder_id, $ts_for_deletion]
                );
                $i++;
            }
        }
    }

    /**
     * @param ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj
     */
    private function sendMail(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj)
    {
        include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsNotification.php';
        $mail = new ilMailCronOrphanedMailsNotification();

        $mail->setRecipients(array($collection_obj->getUserId()));
        $mail->setAdditionalInformation(array('mail_folders' => $collection_obj->getFolderObjects()));
        $mail->send();
    }

    /**
     *
     */
    public function processNotification()
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
