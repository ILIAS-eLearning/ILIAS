<?php

declare(strict_types=1);

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

namespace ILIAS\Mail\Cron\ExpiredOrOrphanedMails;

use ilLoggerFactory;
use ilSetting;
use ilDBStatement;
use ilLogger;
use ilDBConstants;
use ilFileUtils;
use ilMailCronOrphanedMails;
use ilDBInterface;
use Throwable;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SplFileInfo;

class MailDeletionHandler
{
    private const PING_THRESHOLD = 250;
    private ilDBInterface $db;
    private ilSetting $settings;
    private ilLogger $logger;
    private ilDBStatement $mail_ids_for_path_stmt;
    /** @var callable|null */
    private $delete_directory_callback;

    public function __construct(
        private ilMailCronOrphanedMails $job,
        private ExpiredOrOrphanedMailsCollector $collector,
        ?ilDBInterface $db = null,
        ?ilSetting $setting = null,
        ?ilLogger $logger = null,
        ?callable $delete_directory_callback = null
    ) {
        global $DIC;

        $this->db = $db ?? $DIC->database();
        $this->settings = $setting ?? $DIC->settings();
        $this->logger = $logger ?? ilLoggerFactory::getLogger('mail');
        $this->delete_directory_callback = $delete_directory_callback;

        $this->mail_ids_for_path_stmt = $this->db->prepare(
            'SELECT COUNT(*) cnt FROM mail_attachment WHERE path = ?',
            [ilDBConstants::T_TEXT]
        );
    }

    /**
     * @return string[]
     */
    private function determineDeletableAttachmentPaths(): array
    {
        $attachment_paths = [];

        $res = $this->db->query(
            '
				SELECT path, COUNT(mail_id) cnt_mail_ids
				FROM mail_attachment 
				WHERE ' . $this->db->in(
                'mail_id',
                $this->collector->mailIdsToDelete(),
                false,
                ilDBConstants::T_INTEGER
            ) . ' GROUP BY path'
        );

        $i = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            if ($i > 0 && $i % self::PING_THRESHOLD) {
                $this->job->ping();
            }

            $num_usages_total = (int) $this->db->fetchAssoc(
                $this->db->execute(
                    $this->mail_ids_for_path_stmt,
                    [$row['path']]
                )
            )['cnt'];
            $num_usages_within_deleted_mails = (int) $row['cnt_mail_ids'];

            if ($num_usages_within_deleted_mails >= $num_usages_total) {
                $attachment_paths[] = $row['path'];
            }

            ++$i;
        }

        return $attachment_paths;
    }

    private function deleteDirectory(string $directory): void
    {
        if ($this->delete_directory_callback !== null) {
            call_user_func($this->delete_directory_callback, $directory);
        } else {
            ilFileUtils::delDir($directory);
        }
    }

    private function deleteAttachments(): void
    {
        $attachment_paths = $this->determineDeletableAttachmentPaths();

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
                    /** @var SplFileInfo $file */

                    $path_name = $file->getPathname();
                    if ($file->isDir()) {
                        $this->deleteDirectory($path_name);
                        $this->logger->info(
                            sprintf(
                                "Attachment directory '%s' deleted",
                                $path_name
                            )
                        );
                    } elseif (is_file($path_name) && unlink($path_name)) {
                        $this->logger->info(
                            sprintf(
                                "Attachment file '%s' deleted",
                                $path_name
                            )
                        );
                    } else {
                        $this->logger->info(
                            sprintf(
                                'Attachment file \'%s\' for mail_id could not be deleted due to missing file system permissions',
                                $path_name
                            )
                        );
                    }
                }

                $this->deleteDirectory($path);
                $this->logger->info(
                    sprintf(
                        "Attachment directory '%s' deleted",
                        $path
                    )
                );
            } catch (Throwable $e) {
                $this->logger->warning($e->getMessage());
                $this->logger->warning($e->getTraceAsString());
            } finally {
                ++$i;
            }
        }

        $this->db->manipulate(
            'DELETE FROM mail_attachment WHERE ' .
            $this->db->in('mail_id', $this->collector->mailIdsToDelete(), false, ilDBConstants::T_INTEGER)
        );
    }

    private function deleteMails(): void
    {
        $this->db->manipulate(
            'DELETE FROM mail WHERE ' .
            $this->db->in('mail_id', $this->collector->mailIdsToDelete(), false, ilDBConstants::T_INTEGER)
        );
    }

    private function deleteMarkedAsNotified(): void
    {
        if ((int) $this->settings->get('mail_notify_orphaned', '0') >= 1) {
            $this->db->manipulate(
                'DELETE FROM mail_cron_orphaned WHERE ' .
                $this->db->in('mail_id', $this->collector->mailIdsToDelete(), false, ilDBConstants::T_INTEGER)
            );
        } else {
            $this->db->manipulate('DELETE FROM mail_cron_orphaned');
        }
    }

    public function delete(): void
    {
        if ($this->collector->mailIdsToDelete() !== []) {
            $this->deleteAttachments();

            $this->deleteMails();

            $this->logger->info(
                sprintf(
                    'Deleted mail_ids: %s',
                    implode(', ', $this->collector->mailIdsToDelete())
                )
            );

            $this->deleteMarkedAsNotified();
            $this->logger->info(
                sprintf(
                    'Deleted mail_cron_orphaned mail_ids: %s',
                    implode(', ', $this->collector->mailIdsToDelete())
                )
            );
        }
    }
}
