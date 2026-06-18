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
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ilMailAttachmentStakeholder;
use ilResourceStorageMigrationHelper;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class MigrateMailAttachmentsToIRSS implements Migration
{
    private const int PATHS_PER_STEP = 5;
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

    public function getRemainingAmountOfSteps(): int
    {
        return (int) $this->helper->getDatabase()->fetchObject(
            $this->helper->getDatabase()->query(
                'SELECT COUNT(DISTINCT path) cnt FROM mail_attachment
                 WHERE (rcid IS NULL OR rcid = "")
                 AND path IS NOT NULL AND path != ""'
            )
        )->cnt;
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
