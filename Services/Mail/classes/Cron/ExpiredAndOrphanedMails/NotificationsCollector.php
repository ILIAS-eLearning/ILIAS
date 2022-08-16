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

namespace ILIAS\Mail\Cron\ExpiredAndOrphanedMails;

use ILIAS\Data\Factory;
use ILIAS\Data\Clock\ClockInterface;
use ExpiredOrOrphanedMailsReportDto;
use ilSetting;
use ilDBConstants;
use ilMailCronOrphanedMails;
use ilDBInterface;

class NotificationsCollector
{
    private const PING_THRESHOLD = 500;

    private ilMailCronOrphanedMails $job;
    /** @var array<int, ExpiredOrOrphanedMailsReportDto> */
    private array $collection = [];
    private ilDBInterface $db;
    private ilSetting $setting;
    private ClockInterface $clock;

    public function __construct(
        ilMailCronOrphanedMails $job,
        ?ilDBInterface $db = null,
        ?ilSetting $settings = null,
        ?ClockInterface $clock = null
    ) {
        global $DIC;

        $this->db = $db ?? $DIC->database();
        $this->setting = $settings ?? $DIC->settings();
        $this->clock = $clock ?? (new Factory())->clock()->system();

        $this->job = $job;

        $this->collect();
    }

    private function collect() : void
    {
        $mail_expiration_days = (int) $this->setting->get('mail_threshold', '0');
        $mail_expiration_warning_days = (int) $this->setting->get('mail_notify_orphaned', '0');

        if ($mail_expiration_days > $mail_expiration_warning_days) {
            $notify_days_before = $mail_expiration_days - $mail_expiration_warning_days;
        } else {
            $notify_days_before = 1;
        }

        $left_interval_datetime = $this->clock->now()->modify('- ' . $notify_days_before . ' days');

        $types = [ilDBConstants::T_TIMESTAMP];
        $data = [$left_interval_datetime->format('Y-m-d 23:59:59')];

        $notification_query = "
            SELECT 		m.mail_id, m.user_id, m.folder_id, m.send_time, m.m_subject, mdata.title
            FROM 		mail m
            LEFT JOIN 	mail_obj_data mdata ON mdata.obj_id = m.folder_id
            LEFT JOIN   mail_cron_orphaned mco ON mco.mail_id = m.mail_id
            WHERE 		mco.mail_id IS NULL AND m.send_time <= %s
        ";

        if ((int) $this->setting->get('mail_only_inbox_trash', '0') > 0) {
            $notification_query .= " AND ((mdata.m_type = %s OR mdata.m_type = %s) OR mdata.obj_id IS NULL)";
            array_push($types, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT);
            array_push($data, 'inbox', 'trash');
        }

        $notification_query .= " ORDER BY m.user_id, m.folder_id, m.mail_id";

        /** @var null|ExpiredOrOrphanedMailsReportDto $collection_obj */
        $collection_obj = null;

        $res = $this->db->queryF($notification_query, $types, $data);
        $i = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            if ($i > 0 && $i % self::PING_THRESHOLD === 0) {
                $this->job->ping();
            }

            if ($collection_obj !== null && !$this->existsCollectionObjForUserId((int) $row['user_id'])) {
                // The user changed, so we'll have to set the collection to NULL after adding it to the queue
                $collection_obj = null;
            }

            if ($collection_obj === null) {
                // For the first user or if the user changed, we'll create a new collection object
                $collection_obj = new ExpiredOrOrphanedMailsReportDto((int) $row['user_id']);
                $this->addCollectionObject($collection_obj);
            }

            $folder_obj = $collection_obj->getFolderObjectById((int) $row['folder_id']);
            if ($folder_obj === null) {
                $folder_obj = new FolderDto((int) $row['folder_id'], $row['title']);
                $collection_obj->addFolderObject($folder_obj);
            }

            $orphaned_mail_obj = new MailDto(
                (int) $row['mail_id'],
                $row['m_subject']
            );
            $folder_obj->addMailObject($orphaned_mail_obj);
            ++$i;
        }
    }

    private function existsCollectionObjForUserId(int $user_id) : bool
    {
        return isset($this->collection[$user_id]);
    }

    private function addCollectionObject(ExpiredOrOrphanedMailsReportDto $collection_obj) : void
    {
        $this->collection[$collection_obj->getUserId()] = $collection_obj;
    }

    /**
     * @return array<int, ExpiredOrOrphanedMailsReportDto>
     */
    public function getCollection() : array
    {
        return $this->collection;
    }
}
