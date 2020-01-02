<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';

/**
 * ilMailCronOrphanedMailsDeletionProcessor
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMailsDeletionProcessor
{
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

    /**
     * @param ilMailCronOrphanedMailsDeletionCollector $collector
     */
    public function __construct(ilMailCronOrphanedMailsDeletionCollector $collector)
    {
        global $DIC;

        $this->collector = $collector;

        $this->settings  = $DIC->settings();
        $this->db        = $DIC->database();
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
        
        while ($row = $this->db->fetchAssoc($res)) {
            $usage_res = $this->db->queryF(
                'SELECT mail_id, path FROM mail_attachment WHERE path = %s',
                array('text'),
                array($row['path'])
            );

            $num_rows = $this->db->numRows($usage_res);
            if ($row['cnt_mail_ids'] >= $num_rows) {
                // collect path to delete attachment file
                $attachment_paths[$row['mail_id']] = $row['path'];
            }
        }

        foreach ($attachment_paths as $mail_id => $path) {
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
                            'Attachment directory (%s) deleted for mail_id: %s',
                            $path_name,
                            $mail_id
                        ));
                    } else {
                        if (file_exists($path_name) && unlink($path_name)) {
                            ilLoggerFactory::getLogger('mail')->info(sprintf(
                                'Attachment file (%s) deleted for mail_id: %s',
                                $path_name,
                                $mail_id
                            ));
                        } else {
                            ilLoggerFactory::getLogger('mail')->info(sprintf(
                                'Attachment file (%s) for mail_id could not be deleted due to missing file system permissions: %s',
                                $path_name,
                                $mail_id
                            ));
                        }
                    }
                }

                ilUtil::delDir($path);
                ilLoggerFactory::getLogger('mail')->info(sprintf(
                    'Attachment directory (%s) deleted for mail_id: %s',
                    $path,
                    $mail_id
                ));
            } catch (Exception $e) {
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
