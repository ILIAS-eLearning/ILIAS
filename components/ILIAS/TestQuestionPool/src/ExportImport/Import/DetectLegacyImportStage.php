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
 * @deprecated This stage is only used for legacy imports and will be removed with further ILIAS versions.
 */
class DetectLegacyImportStage implements ImportStage
{
    public function __construct(
        private readonly LoggerInterface $log,
    ) {
    }

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
        $import_name = basename($context->importBaseDir());

        $xml_file = $context->importBaseDir() . DIRECTORY_SEPARATOR . "{$import_name}.xml";
        $qti_file = $context->importBaseDir() . DIRECTORY_SEPARATOR . str_replace(['_qpl_', '_tst_'], '_qti_', $import_name) . '.xml';

        if (!file_exists($qti_file) || !file_exists($xml_file)) {
            $this->log->debug("No legacy import files found for {$import_name}");
            return StageResult::advance($context);
        }

        $this->log->info("Detected legacy import files for {$import_name}");
        return StageResult::advance(
            $context->withLegacyQtiFile($qti_file)->withLegacyXmlFile($xml_file)
        );
    }
}
