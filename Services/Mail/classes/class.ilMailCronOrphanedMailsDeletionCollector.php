<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsDeletionCollector
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsDeletionCollector
{
    private const PING_THRESHOLD = 500;

    private $job;
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilSetting
     */
    protected $settings;
    
    /**
     * @var array
     */
    protected $mail_ids = array();

    public function __construct(ilMailCronOrphanedMails $job)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->db = $DIC->database();

        $this->job = $job;

        $this->collect();
    }

    /**
     *
     */
    public function collect()
    {
        $mail_only_inbox_trash = (int) $this->settings->get('mail_only_inbox_trash');
        $last_cron_start_ts = (int) $this->settings->get('last_cronjob_start_ts', time());
        $mail_notify_orphaned = (int) $this->settings->get('mail_notify_orphaned');

        $now = time();

        if ($mail_notify_orphaned > 0) {
            if ($last_cron_start_ts != null) {
                if ($mail_only_inbox_trash) {
                    // überprüfen ob die mail in einen anderen Ordner verschoben wurde
                    // selektiere die, die tatsächlich gelöscht werden sollen
                    $res = $this->db->queryF(
                        "
						SELECT mail_id FROM mail_cron_orphaned 
                        LEFT JOIN mail_obj_data mdata ON mdata.obj_id = folder_id
						WHERE ts_do_delete <= %s
						AND ((mdata.m_type = %s OR mdata.m_type = %s) OR mdata.obj_id IS NULL)",
                        array('integer', 'text', 'text'),
                        array($now, 'inbox', 'trash')
                    );
                } else {
                    // selektiere alle zu löschenden mails unabhängig vom ordner..
                    $res = $this->db->queryF(
                        "
					SELECT mail_id FROM mail_cron_orphaned 
					WHERE ts_do_delete <= %s",
                        array('integer'),
                        array($now)
                    );
                }
                
                while ($row = $this->db->fetchAssoc($res)) {
                    $this->addMailIdToDelete($row['mail_id']);
                }
            }
        } else {
            // mails sollen direkt ohne vorheriger notification gelöscht werden.
            $mail_threshold = (int) $this->settings->get('mail_threshold');
            $ts_notify = strtotime("- " . $mail_threshold . " days");
            $ts_for_deletion = date('Y-m-d', $ts_notify) . ' 23:59:59';

            $types = array('timestamp');
            $data = array($ts_for_deletion);

            $mails_query = "
				SELECT 		mail_id
				FROM 		mail m
				INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
				WHERE 		send_time <= %s";

            if ((int) $this->settings->get('mail_only_inbox_trash') > 0) {
                $mails_query .= " AND (mdata.m_type = %s OR mdata.m_type = %s)";
                $types = array('timestamp', 'text', 'text');
                $data = array($ts_for_deletion, 'inbox', 'trash');
            }

            $i = 0;
            $res = $this->db->queryF($mails_query, $types, $data);
            while ($row = $this->db->fetchAssoc($res)) {
                if ($i > 0 && $i % self::PING_THRESHOLD) {
                    $this->job->ping();
                }

                $this->addMailIdToDelete($row['mail_id']);

                ++$i;
            }
        }
    }

    /**
     * @param int $mail_id
     */
    public function addMailIdToDelete($mail_id)
    {
        $this->mail_ids[] = (int) $mail_id;
    }

    /**
     * @return array
     */
    public function getMailIdsToDelete()
    {
        return $this->mail_ids;
    }
}
