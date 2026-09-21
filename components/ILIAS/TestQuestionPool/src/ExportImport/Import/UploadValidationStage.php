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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use ILIAS\Language\Language;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ImportStage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\StageResult;
use ilManifestParser;
use Psr\Log\LoggerInterface;

/**
 * First stage of the question pool import pipeline. Receives the uploaded file path from the context, extracts ZIP
 * archives, locates the manifest/XML files, and validates the basic structure before advancing.
 */
class UploadValidationStage implements ImportStage
{
    private const string IMPORT_TEMP_DIR = CLIENT_DATA_DIR . DIRECTORY_SEPARATOR . 'temp';

    public function __construct(
        private readonly Archives $archives,
        private readonly Language $lng,
        private readonly LoggerInterface $log,
        private readonly string $component
    ) {
    }

    public function getIdentifier(): string
    {
        return 'upload_and_validate';
    }

    public function getLabel(): ?string
    {
        return $this->lng->txt('upload');
    }

    public function getDescription(): ?string
    {
        return '';
    }

    public function process(ImportContext $context): StageResult
    {
        if (
            !$context->hasFileToImport()
            || !is_file($context->fileToImport())
            || !str_ends_with(strtolower($context->fileToImport()), '.zip')
        ) {
            $this->log->error("Invalid import file: {$context->fileToImport()}");
            return StageResult::error($context, $this->lng->txt('obj_import_file_error'));
        }

        $subdir = basename($context->fileToImport(), '.zip');
        $import_base_dir = self::IMPORT_TEMP_DIR . DIRECTORY_SEPARATOR . $subdir;

        $options = (new UnzipOptions())->withZipOutputPath(self::IMPORT_TEMP_DIR);
        $handle = fopen($context->fileToImport(), 'r');
        $unzip = $this->archives->unzip(Streams::ofResource($handle), $options);
        $unzip->extract();
        $this->log->info("Extracted import file: {$context->fileToImport()} -> {$import_base_dir}");

        $manifest = new ilManifestParser($import_base_dir . DIRECTORY_SEPARATOR . 'manifest.xml');
        $export_file = array_find(
            $manifest->getExportFiles(),
            fn(array $file): bool => $file['component'] === $this->component
        );

        if ($export_file === null) {
            $this->log->error("No export file found for component: {$this->component}");
            return StageResult::error($context, $this->lng->txt('obj_import_file_error'));
        }

        $component_import_file = $import_base_dir . DIRECTORY_SEPARATOR . $export_file['path'];
        $this->log->info("Found export file for {$this->component}: -> {$component_import_file}");
        $this->log->info("Found valid export file from installation: {$manifest->getInstallId()}");

        return StageResult::advance(
            $context
                ->withComponentImportFile($component_import_file)
                ->withImportBaseDir($import_base_dir)
                ->withInstallId((int) $manifest->getInstallId())
        );
    }
}
