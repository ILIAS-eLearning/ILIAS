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

namespace ILIAS\TestQuestionPool\ExportImport\Import;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ImportStage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\StageResult;
use Psr\Log\LoggerInterface;

/**
 * Final import stage that cleans up the temporary files and directories after successful import or
 * error during import.
 */
class CleanupStage implements ImportStage
{
    public function __construct(
        private readonly LoggerInterface $log,
    ) {
    }

    public function getIdentifier(): string
    {
        return 'cleanup';
    }

    public function getLabel(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function process(ImportContext $context): StageResult
    {
        $file_to_import = $context->get(UploadValidationStage::FILE_TO_IMPORT);
        if ($file_to_import !== null) {
            $temp_dir = dirname($file_to_import);
            if ($temp_dir && file_exists($temp_dir) && is_dir($temp_dir)) {
                $this->removeDirectory($temp_dir);
                $this->log->info("Removed temporary import directory: {$temp_dir}");
            } else {
                $this->log->warning("Temporary import directory does not exist: {$temp_dir}");
            }
        }

        $import_base_dir = $context->get(UploadValidationStage::IMPORT_BASE_DIR);
        if ($import_base_dir && file_exists($import_base_dir) && is_dir($import_base_dir)) {
            $this->removeDirectory($import_base_dir);
            $this->log->info("Removed import target base directory: {$import_base_dir}");
        } else {
            $this->log->warning("Import target base directory does not exist: {$import_base_dir}");
        }

        return StageResult::complete($context);
    }

    private function removeDirectory(string $path): void
    {
        foreach (array_diff(scandir($path), ['.', '..']) as $file) {
            if (is_dir("$path/$file")) {
                $this->removeDirectory("$path/$file");
            } else {
                unlink("$path/$file");
            }
        }

        rmdir($path);
    }
}
