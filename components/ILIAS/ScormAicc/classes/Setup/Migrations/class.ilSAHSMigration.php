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
use ILIAS\Setup\CLI\IOWrapper;

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
    private const TABLE_NAME = 'sahs_lm';

    protected ilResourceStorageMigrationHelper $helper;
    protected ?IOWrapper $io = null;

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
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }
    }

    public function step(Environment $environment): void
    {
        $r = $this->helper->getDatabase()->query(
            "SELECT lm.id, object_data.owner AS owner_id
                FROM " . self::TABLE_NAME . " lm
                LEFT JOIN object_data ON object_data.obj_id = lm.id
                WHERE rid IS NULL OR rid = ''
                LIMIT 1;"
        );

        $d = $this->helper->getDatabase()->fetchObject($r);
        $object_id = (int) ($d->id ?? null);

        $resource_owner_id = (int) ($d->owner_id ?? 6);

        $lm_path = $this->buildBasePath($object_id);

        if (!is_dir($lm_path)) {
            $this->error(
                "Legacy SCORM/AICC directory $lm_path for object id $object_id does not exist. " .
                "Marking record as migration-failed (rid='-')."
            );
            $this->helper->getDatabase()->update(
                self::TABLE_NAME,
                ['rid' => ['text', '-']],
                ['id' => ['integer', $object_id],]
            );
            return;
        }

        try {
            $this->inform(
                "Trying to move SCORM/AICC directory $lm_path for object id $object_id to the storage service."
            );
            $rid = $this->helper->moveDirectoryToContainerResource(
                $lm_path,
                $resource_owner_id
            );
            $this->inform("Migration proceeded without error for object id $object_id.");
        } catch (\Throwable $e) {
            $this->error(
                "Failed to move SCORM/AICC directory $lm_path for object id $object_id " .
                "to the storage service with exception: {$e->getMessage()}"
            );
            $this->error($e->getTraceAsString());
            throw $e;
        }

        if ($rid !== null) {
            $this->inform(
                "IRSS identification for object id $object_id: {$rid->serialize()}",
                true
            );
            $this->helper->getDatabase()->update(
                self::TABLE_NAME,
                ['rid' => ['text', $rid->serialize()]],
                ['id' => ['integer', $object_id],]
            );

            $this->recursiveRmDir($lm_path);
        } else {
            $this->error(
                'IRSS returned NULL as identification when trying to move SCORM/AICC directory ' .
                "$lm_path for object id $object_id to the storage service."
            );
            $this->helper->getDatabase()->update(
                self::TABLE_NAME,
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
            "SELECT COUNT(id) AS amount FROM " . self::TABLE_NAME . " WHERE rid IS NULL OR rid = ''"
        );
        $d = $this->helper->getDatabase()->fetchObject($r) ?? new stdClass();

        return (int) ($d->amount ?? 0);
    }

    protected function buildBasePath(int $object_id): string
    {
        return CLIENT_WEB_DIR . '/lm_data/lm_' . $object_id;
    }

    private function inform(string $text, bool $force = false): void
    {
        if ($this->io === null || (!$force && !$this->io->isVerbose())) {
            return;
        }

        $this->io->inform($text);
    }

    private function error(string $text): void
    {
        if ($this->io === null) {
            return;
        }

        $this->io->error($text);
    }
}
