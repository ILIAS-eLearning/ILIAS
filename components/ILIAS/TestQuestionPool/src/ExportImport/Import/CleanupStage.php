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
use Psr\Http\Message\ServerRequestInterface;

/**
 * Final import stage that cleans up the temporary files and directories after successful import or
 * error during import.
 */
class CleanupStage implements ImportStage
{
    public function getIdentifier(): string
    {
        return 'cleanup';
    }

    public function getLabel(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function process(ImportContext $context, ServerRequestInterface $request): StageResult
    {
        $file_to_import = $context->get('file_to_import');
        if ($file_to_import !== null) {
            $temp_dir = dirname($file_to_import);
            if (file_exists($temp_dir) && is_dir($temp_dir)) {
                $this->removeDirectory($temp_dir);
            }
        }

        $import_base_dir = $context->get('import_base_dir');
        if (file_exists($import_base_dir) && is_dir($import_base_dir)) {
            $this->removeDirectory($import_base_dir);
        }

        return StageResult::complete($context);
    }

    private function removeDirectory(string $path): void
    {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir("$path/$file")) {
                $this->removeDirectory("$path/$file");
            } else {
                unlink("$path/$file");
            }
        }

        rmdir($path);
    }
}
