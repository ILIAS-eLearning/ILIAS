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

class ilBadgesFilesMigration implements Migration
{
    const TABLE_NAME = 'badge_badge';

    protected ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return 'Migration of files of badges to the resource storage service.';
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
        ilContext::init(ilContext::CONTEXT_CRON);
        ilInitialisation::initILIAS();
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilBadgeFileStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {

        $r = $this->helper->getDatabase()->query(
            "SELECT id, image, image_rid  FROM  " . self::TABLE_NAME . "
                             WHERE  image_rid IS NULL OR image_rid = '' 
                             LIMIT 1"
        );

        $d = $this->helper->getDatabase()->fetchObject($r);
        $id = (int) $d->id;
        $image = $d->image;

        if($image !== '' && $image !== null) {
            $image = $this->getImagePath($id, $image);
            $base_path = dirname($image);
            $pattern = '/(.+)/m';

            if (is_dir($base_path) &&  file_exists($image) && count(scandir($base_path)) > 2) {
                $collection_id = $this->helper->moveFilesOfPatternToCollection(
                    $base_path,
                    $pattern,
                    ResourceCollection::NO_SPECIFIC_OWNER,
                    ResourceCollection::NO_SPECIFIC_OWNER,
                    null,
                    $this->getRevisionNameCallback()
                );

            }
            $save_collection_id = $collection_id === null ? '-' : $collection_id->serialize();

            $this->helper->getDatabase()->update(
                self::TABLE_NAME,
                [
                    'image_rid' => ['text', $save_collection_id],
                    'image' => ['text', null]
                ],
                ['id' => ['integer', $id]]
            );
        }
    }

    public function getImagePath(
        int $id,
        string $image,
        bool $a_full_path = true
    ): string {
        if ($id) {
            $exp = explode(".", $image);
            $suffix = strtolower(array_pop($exp));
            if ($a_full_path) {
                return $this->getFilePath($id) . "img" . $id . "." . $suffix;
            }
            return "img" . $id . "." . $suffix;
        }

        return "";
    }

    protected function getFilePath(
        int $a_id,
        string $a_subdir = null
    ): string {
        $storage = new ilFSStorageBadge($a_id);
        $storage->create();
        $path = $storage->getAbsolutePath() . "/";

        if ($a_subdir) {
            $path .= $a_subdir . "/";

            if (!is_dir($path)) {
                mkdir($path);
            }
        }
        return $path;
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT count(id) as amount FROM  " . self::TABLE_NAME . "
                             WHERE  image_rid IS NULL OR image_rid = '';"
        );
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int) $d->amount;
    }

    public function getRevisionNameCallback(): Closure
    {
        return static function (string $file_name): string {
            return md5($file_name);
        };
    }

}
