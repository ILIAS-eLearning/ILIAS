<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailNotifier
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotifier
{
    protected ilMailCronOrphanedMailsNotificationCollector $collector;
    protected ilDBInterface $db;
    protected int $threshold = 0;
    protected int $mail_notify_orphaned = 0;

    public function __construct(
        ilMailCronOrphanedMailsNotificationCollector $collector,
        int $threshold,
        int $mail_notify_orphaned
    ) {
        global $DIC;

        $this->db = $DIC->database();

        $this->collector = $collector;
        $this->threshold = $threshold;
        $this->mail_notify_orphaned = $mail_notify_orphaned;
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

        foreach ($collection_obj->getFolderObjects() as $folder_obj) {
            $folder_id = $folder_obj->getFolderId();
            
            foreach ($folder_obj->getOrphanedMailObjects() as $mail_obj) {
                $mail_id = $mail_obj->getMailId();
            
                $this->db->insert(
                    'mail_cron_orphaned',
                    [
                        'mail_id' => ['integer', $mail_id],
                        'folder_id' => ['integer', $folder_id],
                        'ts_do_delete' => ['integer', $ts_for_deletion], ]
                );
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
        foreach ($this->collector->getCollection() as $collection_obj) {
            $this->sendMail($collection_obj);
            $this->markAsNotified($collection_obj);
        }
    }
}
