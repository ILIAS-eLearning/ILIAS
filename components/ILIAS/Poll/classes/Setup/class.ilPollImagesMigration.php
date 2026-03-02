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

use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\Poll\Image\Repository\Stakeholder\Handler as ilPollImageRepositoryStakeholder;
use ILIAS\Setup\CLI\IOWrapper;

class ilPollImagesMigration implements Migration
{
    protected ilDBInterface $db;
    protected ?IOWrapper $io = null;

    public function getLabel(): string
    {
        return "PollImagesMigration";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 5;
    }

    public function getPreconditions(
        Environment $environment
    ): array {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(
        Environment $environment
    ): void {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }
    }

    public function step(
        Environment $environment
    ): void {
        $res = $this->db->query("SELECT id, image FROM il_poll WHERE migrated = 0 LIMIT 1");
        $row = $res->fetchAssoc();
        $image = $row["image"] ?? "";
        $id = (int) $row["id"];

        if ($image === "") {
            $this->db->manipulate("UPDATE il_poll SET migrated = 1 WHERE id = " . $this->db->quote($id, ilDBConstants::T_INTEGER));
            return;
        }

        $file_path = $this->getImageFullPath($image, $id);
        $thumbnail_path = $this->getThumbnailImagePath($image, $id);
        $org_path = $this->getOrgImagePath($image, $id);
        $stakeholder = (new ilPollImageRepositoryStakeholder())->withUserId(6);

        $irss_helper = new ilResourceStorageMigrationHelper($stakeholder, $environment);
        $rid = $irss_helper->movePathToStorage($file_path, 6, null, null, false);
        $rid_thumbnail = $irss_helper->movePathToStorage($thumbnail_path, 6, null, null, false);
        $org_thumbnail = $irss_helper->movePathToStorage($org_path, 6, null, null, false);

        $res_existing = $this->db->query("SELECT * FROM il_poll_image WHERE object_id = " . $this->db->quote($id, ilDBConstants::T_INTEGER));
        $row_existing = $res_existing->fetchAssoc();
        if (!is_null($rid)) {
            if (is_null($row_existing)) {
                $this->db->manipulate(
                    "INSERT INTO il_poll_image (object_id, rid) VALUES "
                    . " (" . $this->db->quote($id, ilDBConstants::T_INTEGER)
                    . ", " . $this->db->quote($rid->serialize(), ilDBConstants::T_TEXT) . ")"
                );
            } else {
                $irss_helper->getResourceBuilder()->remove($irss_helper->getResourceBuilder()->get($rid), $stakeholder);
            }
        } else {
            $this->logError('Image ' . $file_path . ' of poll with object ID ' . $id . ' could not be moved to storage.');
        }

        if (!is_null($rid_thumbnail)) {
            $irss_helper->getResourceBuilder()->remove($irss_helper->getResourceBuilder()->get($rid_thumbnail), $stakeholder);
        }
        if (!is_null($org_thumbnail)) {
            $irss_helper->getResourceBuilder()->remove($irss_helper->getResourceBuilder()->get($org_thumbnail), $stakeholder);
        }

        $this->db->manipulate("UPDATE il_poll SET migrated = 1 WHERE id = " . $this->db->quote($id, ilDBConstants::T_INTEGER));
    }

    public function getRemainingAmountOfSteps(): int
    {
        $res = $this->db->query(
            'SELECT COUNT(*) AS count FROM il_poll WHERE migrated = 0'
        );
        $row = $this->db->fetchAssoc($res);
        return (int) $row['count'];
    }

    public function getImageFullPath(string $img, int $id): ?string
    {
        return $this->getLegacyPath($id) . '/' . $img;
    }

    protected function getThumbnailImagePath(string $img, int $id): string
    {
        return $this->getLegacyPath($id) . "/thb_" . $img;
    }

    protected function getOrgImagePath(string $img, int $id): string
    {
        return $this->getLegacyPath($id) . "/org_" . $img;
    }

    protected function getLegacyPath(int $a_id): string
    {
        $path = 'sec/ilPoll/' . ilFileSystemAbstractionStorage::createPathFromId($a_id, 'poll');
        return rtrim(CLIENT_WEB_DIR, '/') . '/' . rtrim($path, '/');
    }

    protected function logError(string $text): void
    {
        if ($this->io === null) {
            return;
        }

        $this->io->error($text);
    }
}
