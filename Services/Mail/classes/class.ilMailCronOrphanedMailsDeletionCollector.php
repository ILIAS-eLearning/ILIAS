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
 * ilMailCronOrphanedMailsDeletionCollector
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsDeletionCollector
{
    private const PING_THRESHOLD = 500;

    private ilMailCronOrphanedMails $job;
    private ilDBInterface $db;
    private ilSetting $settings;
    /** @var int[] */
    private array $mail_ids = [];

    public function __construct(ilMailCronOrphanedMails $job)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->db = $DIC->database();

        $this->job = $job;

        $this->collect();
    }

    public function collect() : void
    {
        $mail_only_inbox_trash = (int) $this->settings->get('mail_only_inbox_trash', '0');
        $last_cron_start_ts = (int) $this->settings->get('last_cronjob_start_ts', (string) time());
        $mail_notify_orphaned = (int) $this->settings->get('mail_notify_orphaned', '0');

        $now = time();

        if ($mail_notify_orphaned > 0) {
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
        }

        $i = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            if ($i > 0 && $i % self::PING_THRESHOLD) {
                $this->job->ping();
            }

            $this->addMailIdToDelete((int) $row['mail_id']);

            ++$i;
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
