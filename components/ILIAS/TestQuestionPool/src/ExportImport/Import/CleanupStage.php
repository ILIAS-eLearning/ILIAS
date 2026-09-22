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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Import\ImportStage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Import\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Import\StageResult;
use Psr\Log\LoggerInterface;

/**
 * Final import stage that cleans up the temporary files and directories after successful import or
 * error during import.
 */
class CleanupStage implements ImportStage
{
    public function __construct(private readonly LoggerInterface $log) {
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
        if ($context->hasFileToImport()) {
            $temp_dir = dirname($context->fileToImport());
            if ($temp_dir !== '' && file_exists($temp_dir) && is_dir($temp_dir)) {
                $this->removeDirectory($temp_dir);
                $this->log->info("Removed temporary import directory: {$temp_dir}");
            } else {
                $this->log->warning("Temporary import directory does not exist: {$temp_dir}");
            }
        }

        if ($context->hasImportBaseDir() && is_dir($context->importBaseDir())) {
            $this->removeDirectory($context->importBaseDir());
            $this->log->info("Removed import target base directory: {$context->importBaseDir()}");
        } else {
            $this->log->warning("Import target base directory does not exist: {$context->importBaseDir()}");
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
