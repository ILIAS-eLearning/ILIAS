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
    public const string FILE_TO_IMPORT = 'file_to_import';
    public const string COMPONENT_IMPORT_FILE = 'component_import_file';
    public const string IMPORT_BASE_DIR = 'import_base_dir';
    public const string INSTALL_ID = 'install_id';

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
        $file_to_import = $context->get(self::FILE_TO_IMPORT);
        if (
            $file_to_import === null
            || !is_file($file_to_import)
            || !str_ends_with(strtolower($file_to_import), '.zip')
        ) {
            $this->log->error("Invalid import file: {$file_to_import}");
            return StageResult::error($context, $this->lng->txt('obj_import_file_error'));
        }

        $subdir = basename($file_to_import, '.zip');
        $import_base_dir = self::IMPORT_TEMP_DIR . DIRECTORY_SEPARATOR . $subdir;

        $options = (new UnzipOptions())->withZipOutputPath(self::IMPORT_TEMP_DIR);
        $unzip = $this->archives->unzip(Streams::ofResource(fopen($file_to_import, 'r')), $options);
        $unzip->extract();
        $this->log->info("Extracted import file: {$file_to_import} -> {$import_base_dir}");

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
                ->with(self::COMPONENT_IMPORT_FILE, $component_import_file)
                ->with(self::IMPORT_BASE_DIR, $import_base_dir)
                ->with(self::INSTALL_ID, $manifest->getInstallId())
        );
    }

    public static function getInstallId(ImportContext $context): int
    {
        return intval($context->get(self::INSTALL_ID));
    }
}
