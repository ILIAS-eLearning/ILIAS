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

/**
 * @deprecated This stage is only used for legacy imports and will be removed with further ILIAS versions.
 */
class DetectLegacyImportStage implements ImportStage
{
    public const string LEGACY_QTI_FILE = 'legacy_qti_file';
    public const string LEGACY_XML_FILE = 'legacy_xml_file';

    public function getIdentifier(): string
    {
        return 'detect_legacy_import';
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
        $import_base_dir = $context->get(UploadValidationStage::IMPORT_BASE_DIR);
        $import_name = basename($import_base_dir);

        $xml_file = $import_base_dir . DIRECTORY_SEPARATOR . $import_name . '.xml';
        $qti_file = $import_base_dir . DIRECTORY_SEPARATOR . str_replace(['_qpl_', '_tst_'], '_qti_', $import_name) . '.xml';

        if (!file_exists($qti_file) || !file_exists($xml_file)) {
            return StageResult::advance($context);
        }

        return StageResult::advance(
            $context->with(self::LEGACY_QTI_FILE, $qti_file)
                ->with(self::LEGACY_XML_FILE, $xml_file)
        );
    }

    public static function isLegacyImport(ImportContext $context): bool
    {
        return $context->has(self::LEGACY_QTI_FILE) && $context->has(self::LEGACY_XML_FILE);
    }
}
