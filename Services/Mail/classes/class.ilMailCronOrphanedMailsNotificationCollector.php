<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsNotificationCollectionObj.php';
include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsFolderObject.php';
include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsFolderMailObject.php';

/**
 * ilMailCronOrphanedMailsNotificationCollector
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsNotificationCollector
{
    private const PING_THRESHOLD = 500;
    /**
     * @var array ilMailCronOrphanedMailsNotificationCollectionObj[]
     */
    protected $collection = array();

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilSetting
     */
    protected $setting;
    private $job;

    public function __construct(ilMailCronOrphanedMails $job)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->setting = $DIC->settings();

        $this->job = $job;

        $this->collect();
    }

    /**
     *
     */
    public function collect()
    {
        $mail_notify_orphaned = (int) $this->setting->get('mail_notify_orphaned');
        $mail_threshold = (int) $this->setting->get('mail_threshold');
        
        if ($mail_threshold > $mail_notify_orphaned) {
            $notify_days_before = $mail_threshold - $mail_notify_orphaned;
        } else {
            $notify_days_before = 1;
        }

        $ts_notify = strtotime("- " . $notify_days_before . " days");
        $ts_for_notification = date('Y-m-d', $ts_notify) . ' 23:59:59';

        $types = array('timestamp');
        $data = array($ts_for_notification);

        $notification_query = "
            SELECT 		m.mail_id, m.user_id, m.folder_id, m.send_time, m.m_subject, mdata.title
            FROM 		mail m
            LEFT JOIN 	mail_obj_data mdata ON mdata.obj_id = m.folder_id
            LEFT JOIN   mail_cron_orphaned mco ON mco.mail_id = m.mail_id
            WHERE 		mco.mail_id IS NULL AND m.send_time <= %s
        ";

        if ((int) $this->setting->get('mail_only_inbox_trash') > 0) {
            $notification_query .= " AND ((mdata.m_type = %s OR mdata.m_type = %s) OR mdata.obj_id IS NULL)";
            $types = array('timestamp', 'text', 'text');
            $data = array($ts_for_notification, 'inbox', 'trash');
        }

        $notification_query .= " ORDER BY m.user_id, m.folder_id, m.mail_id";

        $collection_obj = null;

        $res = $this->db->queryF($notification_query, $types, $data);
        $i = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            if ($i > 0 && $i % self::PING_THRESHOLD === 0) {
                $this->job->ping();
            }

            if ($collection_obj !== null && !$this->existsCollectionObjForUserId($row['user_id'])) {
                // The user changed, so we'll have to set the collection to NULL after adding it to the queue
                $collection_obj = null;
            }

            if ($collection_obj === null) {
                // For the first user or if the user changed, we'll create a new collection object
                $collection_obj = new ilMailCronOrphanedMailsNotificationCollectionObj($row['user_id']);
                $this->addCollectionObject($collection_obj);
            }

            $folder_obj = $collection_obj->getFolderObjectById($row['folder_id']);
            if (!$folder_obj) {
                $folder_obj = new ilMailCronOrphanedMailsFolderObject($row['folder_id']);
                $folder_obj->setFolderTitle($row['title']);
                $collection_obj->addFolderObject($folder_obj);
            }

            $orphaned_mail_obj = new ilMailCronOrphanedMailsFolderMailObject($row['mail_id'], $row['m_subject']);
            $folder_obj->addMailObject($orphaned_mail_obj);
            ++$i;
        }
    }

    /**
     * @param ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj
     */
    public function addCollectionObject(ilMailCronOrphanedMailsNotificationCollectionObj $collection_obj)
    {
        $this->collection[$collection_obj->getUserId()] = $collection_obj;
    }

    /**
     * @param $user_id
     * @return bool
     */
    private function existsCollectionObjForUserId($user_id)
    {
        if (isset($this->collection[$user_id])) {
            return true;
        }

        return false;
    }

    /**
     * @return ilMailCronOrphanedMailsNotificationCollectionObj[]
     */
    public function getCollection()
    {
        return $this->collection;
    }
}
