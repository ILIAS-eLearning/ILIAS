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

use ilDBConstants;
use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ilMailAttachmentStakeholder;
use ilResourceStorageMigrationHelper;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class MigrateMailAttachmentsToIRSS implements Migration
{
    private const int PATHS_PER_STEP = 5;
    private const int MAILS_PER_STEP = 5;
    private ?ilResourceStorageMigrationHelper $helper = null;

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
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilMailAttachmentStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $this->migrateSentAttachmentDirectories();
        $this->migrateSerializedMailAttachments();
    }

    private function migrateSentAttachmentDirectories(): void
    {
        $db = $this->helper->getDatabase();
        $res = $db->query(
            'SELECT path FROM mail_attachment
             WHERE (rcid IS NULL OR rcid = "")
             AND path IS NOT NULL AND path != ""
             GROUP BY path
             LIMIT ' . self::PATHS_PER_STEP
        );

        $mail_path = rtrim($this->helper->getClientDataDir(), '/') . '/mail';

        while ($row = $db->fetchObject($res)) {
            $relative_path = (string) $row->path;
            $absolute_path = $mail_path . '/' . $relative_path;

            if (!is_dir($absolute_path)) {
                $this->markPathAsSkipped($relative_path);

                continue;
            }

            $owner_id = $this->resolveOwnerIdForPath($relative_path);
            $rcid = $this->helper->moveFilesOfPathToCollection(
                $absolute_path,
                $owner_id,
                $owner_id
            );

            if ($rcid === null) {
                $this->markPathAsSkipped($relative_path);

                continue;
            }

            $this->assignRcidToPath($relative_path, $rcid);
            $this->updateMailAttachmentFields($relative_path, $rcid);
        }
    }

    private function migrateSerializedMailAttachments(): void
    {
        $db = $this->helper->getDatabase();
        $res = $db->query(
            'SELECT mail_id, user_id, attachments FROM mail
             WHERE attachments LIKE ' . $db->quote('a:%', 'text') . '
             LIMIT ' . self::MAILS_PER_STEP
        );

        $mail_path = rtrim($this->helper->getClientDataDir(), '/') . '/mail';

        while ($row = $db->fetchObject($res)) {
            $attachments = MailAttachments::fromDb((string) $row->attachments);
            if ($attachments === null || !$attachments->isLegacy()) {
                $this->clearMailAttachmentsColumn((int) $row->mail_id);

                continue;
            }

            $rcid = $this->migratePoolFilenamesToCollection(
                $attachments->legacyFilenames(),
                (int) $row->user_id,
                $mail_path
            );

            if ($rcid === null) {
                $this->clearMailAttachmentsColumn((int) $row->mail_id);

                continue;
            }

            $db->update(
                'mail',
                [
                    'attachments' => [ilDBConstants::T_CLOB, $rcid->serialize()],
                ],
                [
                    'mail_id' => [ilDBConstants::T_INTEGER, (int) $row->mail_id],
                ]
            );
        }
    }

    /**
     * @param list<string> $filenames
     */
    private function migratePoolFilenamesToCollection(
        array $filenames,
        int $user_id,
        string $mail_path
    ): ?ResourceCollectionIdentification {
        $collection = $this->helper->getCollectionBuilder()->new($user_id);

        foreach ($filenames as $filename) {
            $absolute_path = $mail_path . '/' . $user_id . '_' . $filename;
            if (!is_file($absolute_path)) {
                continue;
            }

            $resource_id = $this->helper->movePathToStorage(
                $absolute_path,
                $user_id,
                null,
                static fn(): string => md5($filename)
            );

            if ($resource_id instanceof ResourceIdentification) {
                $collection->add($resource_id);
            }
        }

        if ($collection->count() === 0) {
            return null;
        }

        if (!$this->helper->getCollectionBuilder()->store($collection)) {
            return null;
        }

        return $collection->getIdentification();
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

        $path_count = (int) $db->fetchObject(
            $db->query(
                'SELECT COUNT(DISTINCT path) cnt FROM mail_attachment
                 WHERE (rcid IS NULL OR rcid = "")
                 AND path IS NOT NULL AND path != ""'
            )
        )->cnt;

        $mail_count = (int) $db->fetchObject(
            $db->query(
                'SELECT COUNT(mail_id) cnt FROM mail
                 WHERE attachments LIKE ' . $db->quote('a:%', 'text')
            )
        )->cnt;

        return $path_count + $mail_count;
    }

    private function resolveOwnerIdForPath(string $relative_path): int
    {
        $db = $this->helper->getDatabase();
        $res = $db->queryF(
            'SELECT m.sender_id FROM mail_attachment ma
             INNER JOIN mail m ON m.mail_id = ma.mail_id
             WHERE ma.path = %s
             ORDER BY m.send_time ASC
             LIMIT 1',
            [ilDBConstants::T_TEXT],
            [$relative_path]
        );

        $row = $db->fetchObject($res);
        if ($row !== null && (int) $row->sender_id > 0) {
            return (int) $row->sender_id;
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
        $db = $this->helper->getDatabase();
        $res = $db->queryF(
            'SELECT m.mail_id, m.attachments FROM mail_attachment ma
             INNER JOIN mail m ON m.mail_id = ma.mail_id
             WHERE ma.path = %s',
            [ilDBConstants::T_TEXT],
            [$relative_path]
        );

        while ($row = $db->fetchObject($res)) {
            if (!is_string($row->attachments) || $row->attachments === '') {
                continue;
            }
            if (!str_contains($row->attachments, 'a:')) {
                continue;
            }

            $db->update(
                'mail',
                [
                    'attachments' => [ilDBConstants::T_CLOB, $rcid->serialize()],
                ],
                [
                    'mail_id' => [ilDBConstants::T_INTEGER, (int) $row->mail_id],
                ]
            );
        }
    }

    private function markPathAsSkipped(string $relative_path): void
    {
        $this->helper->getDatabase()->manipulateF(
            'UPDATE mail_attachment SET rcid = %s WHERE path = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['-', $relative_path]
        );
    }
}
