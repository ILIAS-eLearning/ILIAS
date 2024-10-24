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

namespace ILIAS\AdvancedMetaData\Setup;

use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilDBConstants;
use ilDBInterface;
use ilFileUtils;
use ILIAS\DI;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ilIniFilesLoadedObjective;
use ILIAS\AdvancedMetaData\Record\File\Repository\Stakeholder\Handler as Stakeholder;
use ilResourceStorageMigrationHelper;

class RecordFilesMigration implements Migration
{
    protected const GLOBAL_KEY = "null";
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return 'RecordFilesMigration';
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
    }

    public function step(
        Environment $environment
    ): void {
        $files = $this->getFiles();
        $object_id = array_key_first($files);
        $file_path = $files[$object_id][0];
        $stakeholder = (new Stakeholder())->withOwnerId(6);
        $irss_helper = new ilResourceStorageMigrationHelper($stakeholder, $environment);
        $rid = $irss_helper->movePathToStorage($file_path, 6, null, null, false);
        $this->db->manipulate(
            "INSERT INTO adv_md_record_files VALUES ("
            . $this->db->quote(($object_id === self::GLOBAL_KEY) ? 0 : $object_id, ilDBConstants::T_INTEGER) . ", "
            . $this->db->quote($rid->serialize(), ilDBConstants::T_TEXT) . ", "
            . $this->db->quote((int) ($object_id === self::GLOBAL_KEY), ilDBConstants::T_INTEGER) . ")"
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $steps = 0;
        foreach ($this->getFiles() as $object_id => $file_paths) {
            $steps += count($file_paths);
        }
        return $steps;
    }

    protected function getExportDir(): string
    {
        return ilFileUtils::getDataDir() . '/ilAdvancedMetaData';
    }

    protected function getFiles(): array
    {
        $files = [];
        $dirs = [];
        if (!is_dir($this->getExportDir())) {
            return $files;
        }
        foreach (scandir($this->getExportDir()) as $file) {
            $matches = [];
            if (
                (!preg_match('/^export_([0-9]+)$/', $file, $matches) && $file !== "export") ||
                in_array($file, ['.', '..', '.DS_Store'])
            ) {
                continue;
            }
            $object_id = count($matches) == 2
                ? (int) $matches[1]
                : self::GLOBAL_KEY;
            $dirs[$object_id] = $this->getExportDir() . DIRECTORY_SEPARATOR . $file;
        }
        foreach ($dirs as $object_id => $dir) {
            $files_in_dir = [];
            foreach (ilFileUtils::getDir($dir) as $file_name => $file_data) {
                if (in_array($file_name, ['.', '..', '.DS_Store'])) {
                    continue;
                }
                $files_in_dir[] = $dir . DIRECTORY_SEPARATOR . $file_name;
            }
            if (!empty($files_in_dir)) {
                $files[$object_id] = $files_in_dir;
            }
        }
        return $files;
    }
}
