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

/**
 * Class ilForumPostingFilesMigration
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Objective;

class ilForumPostingFilesMigration implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return "Migration of Files in Forum Posts to the Resource Storage Service.";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return \ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new \ilResourceStorageMigrationHelper(
            new \ilForumPostingFileStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $db = $this->helper->getDatabase();

        $r = $this->helper->getDatabase()->query(
            "SELECT
    frm_posts.pos_pk AS posting_id,
    frm_posts.pos_author_id AS owner_id,
    frm_data.top_frm_fk AS object_id
FROM frm_posts
JOIN frm_data ON frm_posts.pos_top_fk = frm_data.top_pk
WHERE frm_posts.rcid IS NULL OR frm_posts.rcid = ''
LIMIT 1;"
        );

        $d = $this->helper->getDatabase()->fetchObject($r);
        $posting_id = (int)$d->posting_id;
        $object_id = (int)$d->object_id;
        $resource_owner_id = (int)$d->owner_id;

        $base_path = $this->buildBasePath();
        $filename_pattern = '/^' . $object_id . '\_' . $posting_id . '\_(.*)/m';
        $pattern = '/.*\/' . $object_id . '\_' . $posting_id . '\_(.*)/m';

        $collection_id = $this->helper->moveFilesOfPatternToCollection(
            $base_path,
            $pattern,
            $resource_owner_id,
            ResourceCollection::NO_SPECIFIC_OWNER,
            $this->getFileNameCallback($filename_pattern),
            $this->getRevisionNameCallback()
        );

        $save_colletion_id = $collection_id === null ? '-' : $collection_id->serialize();
        $this->helper->getDatabase()->update(
            'frm_posts',
            ['rcid' => ['text', $save_colletion_id]],
            ['pos_pk' => ['integer', $posting_id],]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT
    count(frm_posts.pos_pk) AS amount
FROM frm_posts
JOIN frm_data ON frm_posts.pos_top_fk = frm_data.top_pk
WHERE frm_posts.rcid IS NULL OR frm_posts.rcid = '';"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int)$d->amount;
    }

    protected function buildBasePath(): string
    {
        return CLIENT_DATA_DIR . '/forum/';
    }

    public function getFileNameCallback(string $pattern): Closure
    {
        return function (string $file_name) use ($pattern): string {
            if (preg_match($pattern, $file_name, $matches)) {
                return $matches[1] ?? $file_name;
            }
            return $file_name;
        };
    }

    public function getRevisionNameCallback(): Closure
    {
        return function (string $file_name): string {
            return md5($file_name);
        };
    }
}
