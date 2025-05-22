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

class ilStyleIRSSMigration implements Migration
{
    protected ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return 'Migration of style images to the resource storage service.';
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
            new ilContentStyleStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT id FROM style_data WHERE rid IS NULL OR rid = '' LIMIT 1;"
        );

        $d = $this->helper->getDatabase()->fetchObject($r);
        $object_id = (int) ($d->id ?? null);

        $resource_owner_id = (int) ($d->owner_id ?? 6); // TODO JOIN

        $style_path = $this->buildBasePath($object_id);

        $rid = $this->helper->moveDirectoryToContainerResource(
            $style_path,
            $resource_owner_id
        );

        if ($rid !== null) {
            $this->helper->getDatabase()->update(
                'style_data',
                ['rid' => ['text', $rid->serialize()]],
                ['id' => ['integer', $object_id],]
            );

            //$this->recursiveRmDir($lm_path);
        } else {
            /*
            $this->helper->getDatabase()->update(
                'style_data',
                ['rid' => ['text', '-']],
                ['id' => ['integer', $object_id],]
            );*/
        }
    }

    private function recursiveRmDir(string $path): void
    {
        // recursively remove directory
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->recursiveRmDir("$path/$file") : unlink("$path/$file");
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT COUNT(id) AS amount FROM style_data WHERE rid IS NULL OR rid = ''"
        );
        $d = $this->helper->getDatabase()->fetchObject($r) ?? new stdClass();

        return (int) ($d->amount ?? 0);
    }

    protected function buildBasePath(int $object_id): string
    {
        return CLIENT_WEB_DIR . '/sty/sty_' . $object_id;
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
