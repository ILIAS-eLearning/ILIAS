<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilMailCronOrphanedMailsDeletionCollector
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsDeletionCollector
{
    protected ilDBInterface $db;
    protected ilSetting $settings;
    /** @var int[] */
    protected array $mail_ids = [];

    public function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->db = $DIC->database();

        $this->collect();
    }

    public function collect() : void
    {
        $mail_only_inbox_trash = (int) $this->settings->get('mail_only_inbox_trash', '0');
        $last_cron_start_ts = (int) $this->settings->get('last_cronjob_start_ts', (string) time());
        $mail_notify_orphaned = (int) $this->settings->get('mail_notify_orphaned', '0');

        $now = time();

        if ($mail_notify_orphaned > 0) {
            if ($last_cron_start_ts !== null) {
                if ($mail_only_inbox_trash) {
                    // überprüfen ob die mail in einen anderen Ordner verschoben wurde
                    // selektiere die, die tatsächlich gelöscht werden sollen
                    $res = $this->db->queryF(
                        "
						SELECT * FROM mail_cron_orphaned 
						INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
						WHERE ts_do_delete <= %s
						AND (mdata.m_type = %s OR mdata.m_type = %s)",
                        ['integer', 'text', 'text'],
                        [$now, 'inbox', 'trash']
                    );
                } else {
                    // selektiere alle zu löschenden mails unabhängig vom ordner..
                    $res = $this->db->queryF(
                        "
					SELECT * FROM mail_cron_orphaned 
					WHERE ts_do_delete <= %s",
                        ['integer'],
                        [$now]
                    );
                }
                
                while ($row = $this->db->fetchAssoc($res)) {
                    $this->addMailIdToDelete((int) $row['mail_id']);
                }
            }
        } else {
            // mails sollen direkt ohne vorheriger notification gelöscht werden.
            $mail_threshold = (int) $this->settings->get('mail_threshold', '0');
            $ts_notify = strtotime("- " . $mail_threshold . " days");
            $ts_for_deletion = date('Y-m-d', $ts_notify) . ' 23:59:59';

            $types = ['timestamp'];
            $data = [$ts_for_deletion];

            $mails_query = "
				SELECT 		mail_id, m.user_id, folder_id, send_time, m_subject, mdata.title
				FROM 		mail m
				INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
				WHERE 		send_time <= %s";

            if ((int) $this->settings->get('mail_only_inbox_trash', '0') > 0) {
                $mails_query .= " AND (mdata.m_type = %s OR mdata.m_type = %s)";
                $types = ['timestamp', 'text', 'text'];
                $data = [$ts_for_deletion, 'inbox', 'trash'];
            }

            $res = $this->db->queryF($mails_query, $types, $data);
            while ($row = $this->db->fetchAssoc($res)) {
                $this->addMailIdToDelete((int) $row['mail_id']);
            }
        }
    }

    public function addMailIdToDelete(int $mail_id) : void
    {
        $this->mail_ids[] = $mail_id;
    }

    /**
     * @return int[]
     */
    public function getMailIdsToDelete() : array
    {
        return $this->mail_ids;
    }
}
