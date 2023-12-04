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

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilForumDraftsFilesMigration implements Migration
{
    protected ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return 'Migration of Files in Forum Drafts to the Resource Storage Service.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilForumPostingFileStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT
    frm_posts_drafts.draft_id AS draft_id,
    frm_posts_drafts.post_author_id AS owner_id
FROM frm_posts_drafts
JOIN frm_data ON frm_posts_drafts.forum_id = frm_data.top_pk
WHERE frm_posts_drafts.rcid IS NULL OR frm_posts_drafts.rcid = ''
LIMIT 1;"
        );

        $d = $this->helper->getDatabase()->fetchObject($r);
        $draft_id = (int) $d->draft_id;
        $resource_owner_id = (int) $d->owner_id;

        $base_path = $this->buildBasePath() . $draft_id . '/';
        $pattern = '/(.+)/m';

        if (is_dir($base_path) && count(scandir($base_path)) > 2) {
            $collection_id = $this->helper->moveFilesOfPatternToCollection(
                $base_path,
                $pattern,
                $resource_owner_id,
                ResourceCollection::NO_SPECIFIC_OWNER,
                null,
                $this->getRevisionNameCallback()
            );

            $save_colletion_id = $collection_id === null ? '-' : $collection_id->serialize();
            $this->helper->getDatabase()->update(
                'frm_posts_drafts',
                ['rcid' => ['text', $save_colletion_id]],
                ['draft_id' => ['integer', $draft_id]]
            );
        } else {
            $this->helper->getDatabase()->update(
                'frm_posts_drafts',
                ['rcid' => ['text', '-']],
                ['draft_id' => ['integer', $draft_id]]
            );
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT
    count(frm_posts_drafts.draft_id) AS amount
FROM frm_posts_drafts
JOIN frm_data ON frm_posts_drafts.forum_id = frm_data.top_pk
WHERE frm_posts_drafts.rcid IS NULL OR frm_posts_drafts.rcid = '';"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int) $d->amount;
    }

    protected function buildBasePath(): string
    {
        return CLIENT_DATA_DIR . '/forum/drafts/';
    }

    public function getFileNameCallback(string $pattern): Closure
    {
        return static function (string $file_name) use ($pattern): string {
            if (preg_match($pattern, $file_name, $matches)) {
                return $matches[1] ?? $file_name;
            }
            return $file_name;
        };
    }

    public function getRevisionNameCallback(): Closure
    {
        return static function (string $file_name): string {
            return md5($file_name);
        };
    }
}
