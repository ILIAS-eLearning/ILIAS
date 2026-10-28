<?php

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

declare(strict_types=1);

namespace ILIAS\Mail\Setup\Migration;

use Throwable;
use ilDBConstants;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\Setup\AdminInteraction;
use ilMailAttachmentStakeholder;
use ilResourceStorageMigrationHelper;
use ilDatabaseUpdateStepsExecutedObjective;
use ILIAS\Mail\Folder\MailFolderType;
use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\Mail\Setup\Database\V12\MailDBUpdateSteps12;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

/**
 * Migrates mail attachments to the IRSS, the attachments of the most recently sent mails first,
 * because these are the ones users access most (e.g. the first page of their inbox).
 */
class MigrateMailAttachmentsToIRSS implements Migration
{
    private const int PATHS_PER_STEP = 25;
    private const int MAILS_PER_STEP = 50;
    private const string SKIPPED_MARKER = '-';

    private ?ilResourceStorageMigrationHelper $helper = null;
    private ?AdminInteraction $io = null;
    private int $step_number = 0;

    /** @var array<string, int> */
    private array $statistics = [];

    public function getLabel(): string
    {
        return 'Migrate Mail Attachments to IRSS';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10;
    }

    public function getPreconditions(Environment $environment): array
    {
        return array_merge(
            ilResourceStorageMigrationHelper::getPreconditions(),
            [new ilDatabaseUpdateStepsExecutedObjective(new MailDBUpdateSteps12())]
        );
    }

    public function prepare(Environment $environment): void
    {
        $this->io = $this->resolveAdminInteraction($environment);
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilMailAttachmentStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $this->io ??= $this->resolveAdminInteraction($environment);
        $this->step_number++;
        $this->statistics = [];
        $started = microtime(true);

        try {
            $this->migrateSentAttachmentDirectories();
            $this->migrateSerializedMailAttachments();
        } catch (Throwable $e) {
            $this->error('Migration aborted', ['step' => $this->step_number], $e);

            throw $e;
        }

        if ($this->statistics !== []) {
            $this->info('Step finished', array_merge(
                ['step' => $this->step_number, 'seconds' => round(microtime(true) - $started, 2)],
                $this->statistics
            ));
        }
    }

    // -----------------------------------------------------------------------------------------------------------
    // Directories of sent and received mails
    // -----------------------------------------------------------------------------------------------------------

    /**
     * Sent and received mails share one directory per sending process. All mails referencing
     * a directory get the same collection, the `mail_attachment` rows are used for reference counting.
     */
    private function migrateSentAttachmentDirectories(): void
    {
        $mail_path = rtrim($this->helper->getClientDataDir(), '/') . '/mail';

        foreach ($this->nextUnmigratedPaths() as $relative_path) {
            $absolute_path = $mail_path . '/' . $relative_path;
            if (trim($relative_path, '/. ') === '' || str_contains($relative_path, '..')) {
                $this->skipPath($relative_path, 'Invalid directory name');

                continue;
            }

            if (!is_dir($absolute_path)) {
                $this->skipPath($relative_path, 'Directory does not exist');

                continue;
            }

            $filenames = $this->listDirectoryFiles($absolute_path);
            if ($filenames === []) {
                $this->skipPath($relative_path, 'No file of the directory could be stored');

                continue;
            }

            $owner_id = $this->resolveOwnerIdForPath($relative_path);
            $rcid = $this->copyDirectoryToCollection($absolute_path, $filenames, $owner_id);
            if ($rcid === null) {
                $this->warning('Directory was not migrated because not every file could be stored', [
                    'path' => $relative_path,
                    'files' => count($filenames),
                ]);

                continue;
            }

            $this->markCollectionResourcesConfidential($rcid);
            $this->assignRcidToPath($relative_path, $rcid);
            $this->updateMailAttachmentFields($relative_path, $rcid);
            $this->deleteMigratedDirectory($absolute_path);
            $this->count('directories_migrated');
        }
    }

    /**
     * Files stay on disk until the collection is stored and the mail rows point at it.
     * A rerun can therefore still read them when this step stops in between.
     *
     * @param list<string> $filenames
     */
    private function copyDirectoryToCollection(
        string $absolute_path,
        array $filenames,
        int $owner_id
    ): ?ResourceCollectionIdentification {
        $collection = $this->helper->getCollectionBuilder()->new($owner_id);
        $copies = [];
        foreach ($filenames as $file_name) {
            $resource_id = $this->helper->movePathToStorage(
                $absolute_path . '/' . $file_name,
                $owner_id,
                null,
                static fn(string $name): string => md5($name),
                true
            );
            if (!$resource_id instanceof ResourceIdentification) {
                $this->discardResources($copies);

                return null;
            }
            $copies[] = $resource_id;
            $collection->add($resource_id);
        }

        if ($collection->count() !== count($filenames)
            || !$this->helper->getCollectionBuilder()->store($collection)) {
            $this->discardResources($copies);

            return null;
        }

        return $collection->getIdentification();
    }

    /**
     * Copies of a directory that could not be migrated completely are removed, the next run copies it again
     *
     * @param list<ResourceIdentification> $resource_ids
     */
    private function discardResources(array $resource_ids): void
    {
        foreach ($resource_ids as $resource_id) {
            $this->helper->getManager()->remove($resource_id, $this->helper->getStakeholder());
        }
    }

    /**
     * @return list<string>
     */
    private function listDirectoryFiles(string $absolute_path): array
    {
        $filenames = [];
        foreach (new \DirectoryIterator($absolute_path) as $file_info) {
            if (!$file_info->isFile()) {
                continue;
            }
            $filenames[] = $file_info->getFilename();
        }
        sort($filenames);

        return $filenames;
    }

    private function deleteMigratedDirectory(string $absolute_path): void
    {
        foreach ($this->listDirectoryFiles($absolute_path) as $file_name) {
            unlink($absolute_path . '/' . $file_name);
        }
        rmdir($absolute_path);
    }

    /**
     * @return list<string> Paths of the most recently sent mails first
     */
    private function nextUnmigratedPaths(): array
    {
        $db = $this->helper->getDatabase();
        $db->setLimit(self::PATHS_PER_STEP);
        $res = $db->query(
            'SELECT ma.path FROM mail_attachment ma
             LEFT JOIN mail m ON m.mail_id = ma.mail_id
             WHERE (ma.rcid IS NULL OR ma.rcid = "")
             AND ma.path IS NOT NULL AND ma.path != ""
             GROUP BY ma.path
             ORDER BY MAX(m.send_time) DESC'
        );

        $paths = [];
        while ($row = $db->fetchAssoc($res)) {
            $paths[] = (string) $row['path'];
        }

        return $paths;
    }

    private function skipPath(string $relative_path, string $reason): void
    {
        $mail_ids = $this->mailIdsOfPath($relative_path);
        $this->markPathAsSkipped($relative_path);
        $this->count('directories_skipped');

        $this->warning('Directory skipped: ' . $reason, [
            'path' => $relative_path,
            'mails' => count($mail_ids),
            'mail_ids' => array_slice($mail_ids, 0, 20),
        ]);
    }

    /**
     * @return list<int>
     */
    private function mailIdsOfPath(string $relative_path): array
    {
        $db = $this->helper->getDatabase();
        $res = $db->queryF(
            'SELECT mail_id FROM mail_attachment WHERE path = %s',
            [ilDBConstants::T_TEXT],
            [$relative_path]
        );

        $mail_ids = [];
        while ($row = $db->fetchAssoc($res)) {
            $mail_ids[] = (int) $row['mail_id'];
        }

        return $mail_ids;
    }

    // -----------------------------------------------------------------------------------------------------------
    // Drafts and scheduled mails referencing the attachment pool
    // -----------------------------------------------------------------------------------------------------------

    /**
     * Drafts and scheduled mails reference files of the user's attachment pool. Mails referencing a directory
     * are handled by migrateSentAttachmentDirectories() and must never be treated as pool references.
     */
    private function migrateSerializedMailAttachments(): void
    {
        $db = $this->helper->getDatabase();
        $db->setLimit(self::MAILS_PER_STEP);
        $res = $db->query(
            'SELECT m.mail_id, m.user_id, m.attachments, fo.m_type FROM mail m
             LEFT JOIN mail_attachment ma ON ma.mail_id = m.mail_id
             LEFT JOIN mail_obj_data fo ON fo.obj_id = m.folder_id
             WHERE ma.mail_id IS NULL
             AND m.attachments LIKE ' . $db->quote('a:%', ilDBConstants::T_TEXT) . '
             ORDER BY m.send_time DESC, m.mail_id DESC'
        );

        $mail_path = rtrim($this->helper->getClientDataDir(), '/') . '/mail';
        while ($row = $db->fetchAssoc($res)) {
            $this->migrateSerializedMail(
                (int) $row['mail_id'],
                (int) $row['user_id'],
                (string) $row['attachments'],
                $mail_path,
                in_array(
                    $row['m_type'] ?? null,
                    [MailFolderType::DRAFTS->value, MailFolderType::OUTBOX->value],
                    true
                )
            );
        }
    }

    private function migrateSerializedMail(
        int $mail_id,
        int $user_id,
        string $raw_attachments,
        string $mail_path,
        bool $references_pool
    ): void {
        $attachments = MailAttachments::fromDb($raw_attachments);
        if ($attachments === null || !$attachments->isLegacy()) {
            if ($attachments === null) {
                $this->warning('Attachments of mail could not be read and have been removed', [
                    'mail_id' => $mail_id,
                    'value' => substr($raw_attachments, 0, 200),
                ]);
            }
            $this->clearMailAttachmentsColumn($mail_id);

            return;
        }

        // Sent and received mails reference a directory. Without one, files of the owner's pool must not be attached.
        if (!$references_pool) {
            $this->warning('Mail outside drafts and outbox has no attachment directory, its attachments have been removed', [
                'mail_id' => $mail_id,
                'files' => $attachments->legacyFilenames(),
            ]);
            $this->clearMailAttachmentsColumn($mail_id);

            return;
        }

        $rcid = $this->migratePoolFilenamesToCollection($attachments->legacyFilenames(), $user_id, $mail_path, $mail_id);
        if ($rcid === null) {
            $this->warning('None of the pool files referenced by the mail exists, its attachments have been removed', [
                'mail_id' => $mail_id,
                'files' => $attachments->legacyFilenames(),
            ]);
            $this->clearMailAttachmentsColumn($mail_id);

            return;
        }

        $db = $this->helper->getDatabase();
        $db->manipulateF(
            'INSERT INTO mail_attachment (mail_id, path, rcid) VALUES (%s, %s, %s)',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$mail_id, '', $rcid->serialize()]
        );
        $db->update(
            'mail',
            ['attachments' => [ilDBConstants::T_CLOB, $rcid->serialize()]],
            ['mail_id' => [ilDBConstants::T_INTEGER, $mail_id]]
        );
        $this->count('drafts_migrated');
    }

    /**
     * Pool files are copied, not moved: The same pool file may be referenced by several drafts and stays
     * available in the user's pool, where it is migrated when it is used the next time.
     *
     * @param list<string> $filenames
     */
    private function migratePoolFilenamesToCollection(
        array $filenames,
        int $user_id,
        string $mail_path,
        int $mail_id
    ): ?ResourceCollectionIdentification {
        $collection = $this->helper->getCollectionBuilder()->new($user_id);

        foreach ($filenames as $filename) {
            $absolute_path = $mail_path . '/' . $user_id . '_' . $filename;
            if ($filename === '' || str_contains($filename, '/') || str_contains($filename, '\\') || !is_file($absolute_path)) {
                $this->warning('Pool file referenced by the mail does not exist', [
                    'mail_id' => $mail_id,
                    'file' => $filename,
                ]);

                continue;
            }

            // Pool files are stored as "<user_id>_<name>" on disk, the users only know the name
            $resource_id = $this->helper->movePathToStorage(
                $absolute_path,
                $user_id,
                static fn(): string => $filename,
                static fn(): string => md5($filename),
                true
            );

            if ($resource_id instanceof ResourceIdentification) {
                $this->markConfidential($resource_id);
                $collection->add($resource_id);
            }
        }

        if ($collection->count() === 0) {
            return null;
        }

        if (!$this->helper->getCollectionBuilder()->store($collection)) {
            $this->discardResources(array_values($collection->getResourceIdentifications()));

            return null;
        }

        return $collection->getIdentification();
    }

    private function markConfidential(ResourceIdentification $identification): void
    {
        $this->helper->getManager()->setConfidential($identification, true);
    }

    private function markCollectionResourcesConfidential(ResourceCollectionIdentification $rcid): void
    {
        foreach ($this->helper->getCollectionBuilder()->get($rcid)->getResourceIdentifications() as $identification) {
            $this->markConfidential($identification);
        }
    }

    private function clearMailAttachmentsColumn(int $mail_id): void
    {
        $this->helper->getDatabase()->update(
            'mail',
            [
                'attachments' => [ilDBConstants::T_CLOB, ''],
            ],
            [
                'mail_id' => [ilDBConstants::T_INTEGER, $mail_id],
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $db = $this->helper->getDatabase();

        $path_count = (int) $db->fetchAssoc(
            $db->query(
                'SELECT COUNT(DISTINCT path) cnt FROM mail_attachment
                 WHERE (rcid IS NULL OR rcid = "")
                 AND path IS NOT NULL AND path != ""'
            )
        )['cnt'];

        $mail_count = (int) $db->fetchAssoc(
            $db->query(
                'SELECT COUNT(m.mail_id) cnt FROM mail m
                 LEFT JOIN mail_attachment ma ON ma.mail_id = m.mail_id
                 WHERE ma.mail_id IS NULL
                 AND m.attachments LIKE ' . $db->quote('a:%', ilDBConstants::T_TEXT)
            )
        )['cnt'];

        $remaining = max(
            (int) ceil($path_count / self::PATHS_PER_STEP),
            (int) ceil($mail_count / self::MAILS_PER_STEP)
        );

        $this->info('Remaining', [
            'directories' => $path_count,
            'drafts' => $mail_count,
            'steps' => $remaining,
        ]);

        return $remaining;
    }

    private function resolveOwnerIdForPath(string $relative_path): int
    {
        $db = $this->helper->getDatabase();
        $db->setLimit(1, 0);
        $res = $db->queryF(
            'SELECT m.sender_id FROM mail_attachment ma
             INNER JOIN mail m ON m.mail_id = ma.mail_id
             WHERE ma.path = %s
             ORDER BY m.send_time ASC',
            [ilDBConstants::T_TEXT],
            [$relative_path]
        );

        $row = $db->fetchAssoc($res);
        if (is_array($row) && (int) $row['sender_id'] > 0) {
            return (int) $row['sender_id'];
        }

        return defined('SYSTEM_USER_ID') ? (int) SYSTEM_USER_ID : 6;
    }

    private function assignRcidToPath(string $relative_path, ResourceCollectionIdentification $rcid): void
    {
        $this->helper->getDatabase()->manipulateF(
            'UPDATE mail_attachment SET rcid = %s WHERE path = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$rcid->serialize(), $relative_path]
        );
    }

    private function updateMailAttachmentFields(string $relative_path, ResourceCollectionIdentification $rcid): void
    {
        $mail_ids = $this->mailIdsOfPath($relative_path);
        if ($mail_ids === []) {
            return;
        }

        $db = $this->helper->getDatabase();
        $db->manipulateF(
            'UPDATE mail SET attachments = %s WHERE ' . $db->in('mail_id', $mail_ids, false, ilDBConstants::T_INTEGER),
            [ilDBConstants::T_TEXT],
            [$rcid->serialize()]
        );
    }

    private function markPathAsSkipped(string $relative_path): void
    {
        $this->helper->getDatabase()->manipulateF(
            'UPDATE mail_attachment SET rcid = %s WHERE path = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [self::SKIPPED_MARKER, $relative_path]
        );
    }

    private function resolveAdminInteraction(Environment $environment): ?AdminInteraction
    {
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);

        return $io instanceof AdminInteraction ? $io : null;
    }

    private function count(string $key, int $amount = 1): void
    {
        $this->statistics[$key] = ($this->statistics[$key] ?? 0) + $amount;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function info(string $message, array $context = []): void
    {
        $this->report('INFO', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function warning(string $message, array $context = []): void
    {
        $this->report('WARNING', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function error(string $message, array $context = [], ?Throwable $exception = null): void
    {
        if ($exception !== null) {
            $context['exception'] = $exception::class . ': ' . $exception->getMessage() .
                ' (' . $exception->getFile() . ':' . $exception->getLine() . ')';
        }

        $this->report('ERROR', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function report(string $level, string $message, array $context): void
    {
        $this->io?->inform(sprintf(
            '[%s] Mail attachment migration %s: %s%s',
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context !== []
                ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR)
                : ''
        ));
    }
}
