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

/**
 * Moves the on-disk content of SCORM/AICC learning modules
 * (public/data/lm_data/lm_<id>) into a container resource of the
 * Resource Storage Service and stores the resulting rid in sahs_lm.
 *
 * Covers all c_type values (scorm, scorm2004, aicc) since they share
 * the same lm_data/lm_<id> directory layout.
 */
class ilSAHSMigration implements Migration
{
    protected ilResourceStorageMigrationHelper $helper;

    public function getLabel(): string
    {
        return 'Migration of SCORM/AICC Learning Modules to the Resource Storage Service.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return array_merge(
            ilResourceStorageMigrationHelper::getPreconditions(),
            [
                new ilDatabaseUpdateStepsExecutedObjective(
                    new \ILIAS\ScormAicc\Setup\ScormAiccDatabaseUpdateSteps()
                )
            ]
        );
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilSAHSStakeholder(),
            $environment
        );
    }

    public function step(Environment $environment): void
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT sahs_lm.id, object_data.owner AS owner_id
                FROM sahs_lm
                LEFT JOIN object_data ON object_data.obj_id = sahs_lm.id
                WHERE rid IS NULL OR rid = ''
                LIMIT 1;"
        );

        $d = $this->helper->getDatabase()->fetchObject($r);
        $object_id = (int) ($d->id ?? null);

        $resource_owner_id = (int) ($d->owner_id ?? 6);

        $lm_path = $this->buildBasePath($object_id);

        $rid = $this->helper->moveDirectoryToContainerResource(
            $lm_path,
            $resource_owner_id
        );

        if ($rid !== null) {
            $this->helper->getDatabase()->update(
                'sahs_lm',
                ['rid' => ['text', $rid->serialize()]],
                ['id' => ['integer', $object_id],]
            );

            $this->recursiveRmDir($lm_path);
        } else {
            $this->helper->getDatabase()->update(
                'sahs_lm',
                ['rid' => ['text', '-']],
                ['id' => ['integer', $object_id],]
            );
        }
    }

    private function recursiveRmDir(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        // recursively remove directory
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->recursiveRmDir("$path/$file") : unlink("$path/$file");
        }
        rmdir($path);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT COUNT(id) AS amount FROM sahs_lm WHERE rid IS NULL OR rid = ''"
        );
        $d = $this->helper->getDatabase()->fetchObject($r) ?? new stdClass();

        return (int) ($d->amount ?? 0);
    }

    protected function buildBasePath(int $object_id): string
    {
        return CLIENT_WEB_DIR . '/lm_data/lm_' . $object_id;
    }
}
