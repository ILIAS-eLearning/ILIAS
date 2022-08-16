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

use ILIAS\Data\Clock\ClockInterface;
use ILIAS\Data\Factory;
use ilSetting;
use ilDBConstants;
use ilMailCronOrphanedMails;
use ilDBInterface;

class ExpiredOrOrphanedMailsCollector
{
    private const PING_THRESHOLD = 500;

    private ilMailCronOrphanedMails $job;
    private ilDBInterface $db;
    private ilSetting $settings;
    private ClockInterface $clock;
    /** @var int[] */
    private array $mail_ids = [];

    public function __construct(
        ilMailCronOrphanedMails $job,
        ?ilDBInterface $db = null,
        ?ilSetting $setting = null,
        ?ClockInterface $clock = null
    ) {
        global $DIC;

        $this->db = $db ?? $DIC->database();
        $this->settings = $setting ?? $DIC->settings();
        $this->clock = $clock ?? (new Factory())->clock()->system();

        $this->job = $job;

        $this->collect();
    }

    private function collect() : void
    {
        $mail_only_inbox_trash = (bool) $this->settings->get('mail_only_inbox_trash', '0');
        $mail_expiration_warning_days = (int) $this->settings->get('mail_notify_orphaned', '0');

        if ($mail_expiration_warning_days > 0) {
            if ($mail_only_inbox_trash) {
                // Only select determine mails which are now located in the inbox or trash folder
                $res = $this->db->queryF(
                    "
                        SELECT mail_id FROM mail_cron_orphaned 
                        LEFT JOIN mail_obj_data mdata ON mdata.obj_id = folder_id
                        WHERE ts_do_delete <= %s AND ((mdata.m_type = %s OR mdata.m_type = %s) OR mdata.obj_id IS NULL)
                    ",
                    [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                    [$this->clock->now()->getTimestamp(), 'inbox', 'trash']
                );
            } else {
                // Select all determined emails independently of the folder
                $res = $this->db->queryF(
                    "SELECT mail_id FROM mail_cron_orphaned WHERE ts_do_delete <= %s",
                    [ilDBConstants::T_INTEGER],
                    [$this->clock->now()->getTimestamp()]
                );
            }
        } else {
            // Mails should be deleted without notification
            $mail_expiration_days = (int) $this->settings->get('mail_threshold', '0');
            $left_interval_datetime = $this->clock->now()->modify('- ' . $mail_expiration_days . ' days');

            $types = [ilDBConstants::T_TIMESTAMP];
            $data = [$left_interval_datetime->format('Y-m-d 23:59:59')];

            $mails_query = "
				SELECT 		m.mail_id
				FROM 		mail m
				LEFT JOIN 	mail_obj_data mdata ON mdata.obj_id = m.folder_id
				WHERE 		m.send_time <= %s
            ";

            if ($mail_only_inbox_trash) {
                $mails_query .= " AND ((mdata.m_type = %s OR mdata.m_type = %s) OR mdata.obj_id IS NULL)";
                array_push($types, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT);
                array_push($data, 'inbox', 'trash');
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
