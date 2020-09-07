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

    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->setting = $DIC->settings();

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

        $res = $this->db->query('SELECT mail_id FROM mail_cron_orphaned');
        $already_notified = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $already_notified[$row['mail_id']] = $row['mail_id'];
        }

        $types = array('timestamp');
        $data = array($ts_for_notification);

        $notification_query = "
				SELECT 		mail_id, m.user_id, folder_id, send_time, m_subject, mdata.title
				FROM 		mail m
				INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
				WHERE 		send_time <= %s";

        if ((int) $this->setting->get('mail_only_inbox_trash') > 0) {
            $notification_query .= " AND (mdata.m_type = %s OR mdata.m_type = %s)";
            $types = array('timestamp', 'text', 'text');
            $data = array($ts_for_notification, 'inbox', 'trash');
        }

        $notification_query .= " AND " . $this->db->in('mail_id', array_values($already_notified), true, 'integer')
            . " ORDER BY m.user_id, folder_id, mail_id";

        $collection_obj = null;
        $folder_obj = null;

        $res = $this->db->queryF($notification_query, $types, $data);
        while ($row = $this->db->fetchAssoc($res)) {
            if (!$this->existsCollectionObjForUserId($row['user_id'])) {
                if (is_object($collection_obj)) {
                    $this->addCollectionObject($collection_obj);
                }
            }

            if (!is_object($collection_obj)) {
                $collection_obj = new ilMailCronOrphanedMailsNotificationCollectionObj($row['user_id']);
            }

            if (is_object($collection_obj)) {
                if (!$folder_obj = $collection_obj->getFolderObjectById($row['folder_id'])) {
                    $folder_obj = new ilMailCronOrphanedMailsFolderObject($row['folder_id']);
                    $folder_obj->setFolderTitle($row['title']);
                    $collection_obj->addFolderObject($folder_obj);
                }

                if (is_object($folder_obj)) {
                    $orphaned_mail_obj = new ilMailCronOrphanedMailsFolderMailObject($row['mail_id'], $row['m_subject']);
                    $folder_obj->addMailObject($orphaned_mail_obj);
                }
            }
        }

        if (is_object($collection_obj)) {
            $this->addCollectionObject($collection_obj);
            unset($collection_obj);
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
