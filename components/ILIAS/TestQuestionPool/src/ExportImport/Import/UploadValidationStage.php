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
use Psr\Http\Message\ServerRequestInterface;

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
        private readonly string $component
    ) {
    }

    public function getIdentifier(): string
    {
        return 'upload_and_validate';
    }

    public function getLabel(): string
    {
        return $this->lng->txt('upload');
    }

    public function getDescription(): string
    {
        return '';
    }

    public function process(ImportContext $context, ServerRequestInterface $request): StageResult
    {
        $file_to_import = $context->get('file_to_import');
        if (
            $file_to_import === null
            || !is_file($file_to_import)
            || !str_ends_with(strtolower($file_to_import), '.zip')
        ) {
            return StageResult::error($context, $this->lng->txt('obj_import_file_error'));
        }

        $subdir = basename($file_to_import, '.zip');
        $import_base_dir = self::IMPORT_TEMP_DIR . DIRECTORY_SEPARATOR . $subdir;

        $options = (new UnzipOptions())->withZipOutputPath(self::IMPORT_TEMP_DIR);
        $unzip = $this->archives->unzip(Streams::ofResource(fopen($file_to_import, 'r')), $options);
        $unzip->extract();

        $manifest = new ilManifestParser($import_base_dir . DIRECTORY_SEPARATOR . 'manifest.xml');
        $export_file = array_find(
            $manifest->getExportFiles(),
            fn($file): bool => $file['component'] === $this->component
        );

        if ($export_file === null) {
            return StageResult::error($context, $this->lng->txt('obj_import_file_error'));
        }

        return StageResult::advance(
            $context->with('import_file', $import_base_dir . DIRECTORY_SEPARATOR . $export_file['path'])
            ->with('install_id', $manifest->getInstallId())
        );
    }

    public static function getInstallId(ImportContext $context): int
    {
        return intval($context->get('install_id'));
    }
}
