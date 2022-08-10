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
 * ilMailCronOrphanedMailsDeletionProcessor
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsDeletionProcessor
{
    private const PING_THRESHOLD = 250;

    private ilMailCronOrphanedMails $job;
    private ilMailCronOrphanedMailsDeletionCollector $collector;
    private ilDBInterface $db;
    private ilSetting $settings;
    private ilDBStatement $mail_ids_for_path_stmt;

    public function __construct(ilMailCronOrphanedMails $job, ilMailCronOrphanedMailsDeletionCollector $collector)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->db = $DIC->database();

        $this->job = $job;
        $this->collector = $collector;

        $this->mail_ids_for_path_stmt = $this->db->prepare(
            'SELECT mail_id, path FROM mail_attachment WHERE path = ?',
            [ilDBConstants::T_TEXT]
        );
    }

    private function deleteAttachments() : void
    {
        $attachment_paths = [];

        $res = $this->db->query('
				SELECT path, COUNT(mail_id) cnt_mail_ids
				FROM mail_attachment 
				WHERE ' . $this->db->in(
            'mail_id',
            $this->collector->getMailIdsToDelete(),
            false,
            'integer'
        ) . ' GROUP BY path');
        
        $i = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            if ($i % self::PING_THRESHOLD) {
                $this->job->ping();
            }

            $usage_res = $this->db->execute(
                $this->mail_ids_for_path_stmt,
                [$row['path']]
            );

            $num_rows_usage = $this->db->fetchAssoc($usage_res);
            if (is_array($num_rows_usage) && $num_rows_usage !== [] && (int) $row['cnt_mail_ids'] >= (int) $num_rows_usage['cnt']) {
                // collect path to delete attachment file
                $attachment_paths[(int) $row['mail_id']] = $row['path'];
            }

            ++$i;
        }

        $i = 0;
        foreach ($attachment_paths as $mail_id => $path) {
            if ($i % self::PING_THRESHOLD) {
                $this->job->ping();
            }

            try {
                $path = CLIENT_DATA_DIR . '/mail/' . $path;
                $iter = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($iter as $file) {
                    /** @var SplFileInfo $file */

                    $path_name = $file->getPathname();
                    if ($file->isDir()) {
                        ilFileUtils::delDir($path_name);
                        ilLoggerFactory::getLogger('mail')->info(sprintf(
                            'Attachment directory (%s) deleted for mail_id: %s',
                            $path_name,
                            $mail_id
                        ));
                    } elseif (is_file($path_name) && unlink($path_name)) {
                        ilLoggerFactory::getLogger('mail')->info(sprintf(
                            'Attachment file (%s) deleted for mail_id: %s',
                            $path_name,
                            $mail_id
                        ));
                    } else {
                        ilLoggerFactory::getLogger('mail')->info(sprintf(
                            'Attachment file (%s) for mail_id could not be deleted' .
                            ' due to missing file system permissions: %s',
                            $path_name,
                            $mail_id
                        ));
                    }
                }

                ilFileUtils::delDir($path);
                ilLoggerFactory::getLogger('mail')->info(sprintf(
                    'Attachment directory (%s) deleted for mail_id: %s',
                    $path,
                    $mail_id
                ));
            } catch (Exception $e) {
                ilLoggerFactory::getLogger('mail')->warning($e->getMessage());
                ilLoggerFactory::getLogger('mail')->warning($e->getTraceAsString());
            } finally {
                ++$i;
            }
        }

        $this->db->manipulate(
            'DELETE FROM mail_attachment WHERE ' .
            $this->db->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer')
        );
    }
    
    private function deleteMails() : void
    {
        $this->db->manipulate(
            'DELETE FROM mail WHERE ' .
            $this->db->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer')
        );
    }

    private function deleteMarkedAsNotified() : void
    {
        if ((int) $this->settings->get('mail_notify_orphaned', '0') >= 1) {
            $this->db->manipulate(
                'DELETE FROM mail_cron_orphaned WHERE ' .
                $this->db->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer')
            );
        } else {
            $this->db->manipulate('DELETE FROM mail_cron_orphaned');
        }
    }

    public function processDeletion() : void
    {
        if (count($this->collector->getMailIdsToDelete()) > 0) {
            // delete possible attachments ...
            $this->deleteAttachments();

            $this->deleteMails();
            ilLoggerFactory::getLogger('mail')->info(sprintf(
                'Deleted mail_ids: %s',
                implode(', ', $this->collector->getMailIdsToDelete())
            ));

            $this->deleteMarkedAsNotified();
            ilLoggerFactory::getLogger('mail')->info(sprintf(
                'Deleted mail_cron_orphaned mail_ids: %s',
                implode(', ', $this->collector->getMailIdsToDelete())
            ));
        }
    }
}
