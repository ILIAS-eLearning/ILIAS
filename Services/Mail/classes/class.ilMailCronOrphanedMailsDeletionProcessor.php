<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';

/**
 * ilMailCronOrphanedMailsDeletionProcessor
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsDeletionProcessor
{
    private const PING_THRESHOLD = 250;

    private $job;
    /**
     * @var ilMailCronOrphanedMailsDeletionCollector
     */
    protected $collector;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilSetting
     */
    protected $settings;
    private $mail_ids_for_path_stmt;

    /**
     * @param ilMailCronOrphanedMailsDeletionCollector $collector
     */
    public function __construct(ilMailCronOrphanedMails $job, ilMailCronOrphanedMailsDeletionCollector $collector)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->db = $DIC->database();

        $this->job = $job;
        $this->collector = $collector;

        $this->mail_ids_for_path_stmt = $this->db->prepare(
            'SELECT COUNT(*) cnt FROM mail_attachment WHERE path = ?',
            [ilDBConstants::T_TEXT]
        );
    }
    
    /**
     *
     */
    private function deleteAttachments()
    {
        $attachment_paths = array();

        $res = $this->db->query('
				SELECT path, COUNT(mail_id) cnt_mail_ids
				FROM mail_attachment 
				WHERE ' . $this->db->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer') . '
				GROUP BY path');
        
        $i = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            if ($i > 0 && $i % self::PING_THRESHOLD) {
                $this->job->ping();
            }

            $usage_res = $this->db->execute(
                $this->mail_ids_for_path_stmt,
                [$row['path']]
            );

            $count_usages_data = $this->db->fetchAssoc($usage_res);
            if (is_array($count_usages_data) && $count_usages_data !== [] && (int) $row['cnt_mail_ids'] >= (int) $count_usages_data['cnt']) {
                // collect path to delete attachment file
                $attachment_paths[] = $row['path'];
            }

            ++$i;
        }

        $i = 0;
        foreach ($attachment_paths as $path) {
            if ($i > 0 && $i % self::PING_THRESHOLD) {
                $this->job->ping();
            }

            try {
                $path = CLIENT_DATA_DIR . '/mail/' . $path;
                $iter = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($iter as $file) {
                    /**
                     * @var $file SplFileInfo
                     */

                    $path_name = $file->getPathname();
                    if ($file->isDir()) {
                        ilUtil::delDir($path_name);
                        ilLoggerFactory::getLogger('mail')->info(sprintf(
                            "Attachment directory '%s' deleted",
                            $path_name
                        ));
                    } else {
                        if (file_exists($path_name) && unlink($path_name)) {
                            ilLoggerFactory::getLogger('mail')->info(sprintf(
                                "Attachment file '%s' deleted",
                                $path_name
                            ));
                        } else {
                            ilLoggerFactory::getLogger('mail')->info(sprintf(
                                "Attachment file '%s' for mail_id could not be deleted " .
                                "due to missing file system permissions",
                                $path_name
                            ));
                        }
                    }
                }

                ilUtil::delDir($path);
                ilLoggerFactory::getLogger('mail')->info(sprintf(
                    "Attachment directory '%s' deleted",
                    $path
                ));
            } catch (Exception $e) {
                ilLoggerFactory::getLogger('mail')->warning($e->getMessage());
                ilLoggerFactory::getLogger('mail')->warning($e->getTraceAsString());
            } finally {
                ++$i;
            }
        }

        $this->db->manipulate('DELETE FROM mail_attachment WHERE ' . $this->db->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer'));
    }
    
    /**
     *
     */
    private function deleteMails()
    {
        $this->db->manipulate('DELETE FROM mail WHERE ' . $this->db->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer'));
    }
    
    /**
     * Delete entries about notification
     */
    private function deleteMarkedAsNotified()
    {
        if ((int) $this->settings->get('mail_notify_orphaned') >= 1) {
            $this->db->manipulate('DELETE FROM mail_cron_orphaned WHERE ' . $this->db->in('mail_id', $this->collector->getMailIdsToDelete(), false, 'integer'));
        } else {
            $this->db->manipulate('DELETE FROM mail_cron_orphaned');
        }
    }
    
    /**
     *
     */
    public function processDeletion()
    {
        if (count($this->collector->getMailIdsToDelete()) > 0) {
            // delete possible attachments ...
            $this->deleteAttachments();

            $this->deleteMails();
            require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
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
