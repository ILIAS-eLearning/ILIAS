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

    private function collect() : void
    {
        $mail_only_inbox_trash = (bool) $this->settings->get('mail_only_inbox_trash', '0');
        $mail_notify_orphaned = (int) $this->settings->get('mail_notify_orphaned', '0');

        $now = time();

        if ($mail_notify_orphaned > 0) {
            if ($mail_only_inbox_trash) {
                // Only select determine mails which are now located in the inbox or trash folder
                $res = $this->db->queryF(
                    "SELECT mail_id FROM mail_cron_orphaned 
                    INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
                    WHERE ts_do_delete <= %s AND (mdata.m_type = %s OR mdata.m_type = %s)",
                    [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                    [$now, 'inbox', 'trash']
                );
            } else {
                // Select all determined emails independently of the folder
                $res = $this->db->queryF(
                    "SELECT mail_id FROM mail_cron_orphaned WHERE ts_do_delete <= %s",
                    [ilDBConstants::T_INTEGER],
                    [$now]
                );
            }
        } else {
            // Mails should be deleted without notification
            $mail_threshold = (int) $this->settings->get('mail_threshold', '0');
            $ts_notify = strtotime("- " . $mail_threshold . " days");
            $ts_for_deletion = date('Y-m-d', $ts_notify) . ' 23:59:59';

            $types = [ilDBConstants::T_TIMESTAMP];
            $data = [$ts_for_deletion];

            $mails_query = "
				SELECT 		mail_id
				FROM 		mail m
				INNER JOIN 	mail_obj_data mdata ON obj_id = folder_id
				WHERE 		send_time <= %s";

            if ($mail_only_inbox_trash) {
                $mails_query .= " AND (mdata.m_type = %s OR mdata.m_type = %s)";
                $types = [ilDBConstants::T_TIMESTAMP, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT];
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

    private function addMailIdToDelete(int $mail_id) : void
    {
        $this->mail_ids[] = $mail_id;
    }

    /**
     * @return int[]
     */
    public function mailIdsToDelete() : array
    {
        return $this->mail_ids;
    }
}
