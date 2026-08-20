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

namespace ILIAS\Test\ExportImport\Import;

use ILIAS\Language\Language;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ImportStage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportSessionRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\StageResult;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\XMLFileDeserializer;
use ILIAS\TestQuestionPool\ExportImport\Import\DetectLegacyImportStage;
use ILIAS\TestQuestionPool\ExportImport\Import\UploadValidationStage;
use ilImport;
use Psr\Log\LoggerInterface;

/**
 * Final stage of the test import process. Imports the head dependencies (user and resource mappings) and then
 * imports the test object and all its dependencies using `ilImport`. It will delegate the import to the
 * `ilTestImporter` class.
 */
class PersistStage implements ImportStage
{
    public function __construct(
        private readonly Language $lng,
        private readonly LoggerInterface $log,
        private readonly int $requested_ref_id,
        private readonly ImportSessionRepository $session
    ) {
    }

    public function getIdentifier(): string
    {
        return 'persist';
    }

    public function getLabel(): ?string
    {
        return $this->lng->txt('qpl_import_step_persist');
    }

    public function getDescription(): ?string
    {
        return '';
    }

    public function process(ImportContext $context): StageResult
    {
        if (!DetectLegacyImportStage::isLegacyImport($context)) {
            if ($result = $this->importMappingsFile($context)) {
                return $result;
            }
        }

        (new ilImport($this->requested_ref_id))->importObject(
            null,
            $context->get(UploadValidationStage::FILE_TO_IMPORT),
            basename($context->get(UploadValidationStage::FILE_TO_IMPORT)),
            'tst',
            'components/ILIAS/Test',
            true,
        );

        // Context is updated by the TestImporter so we need to reload it
        return StageResult::complete($this->session->getContext());
    }

    private function importMappingsFile(ImportContext $context): ?StageResult
    {
        $component_import_dir = dirname($context->get(UploadValidationStage::COMPONENT_IMPORT_FILE));
        $mappings_file = "{$component_import_dir}/mappings.xml";
        if (!is_file($mappings_file)) {
            $this->log->error("Mappings file not found: {$mappings_file}");
            return StageResult::error($context, $this->lng->txt('obj_import_file_error'));
        }

        $deserializer = new XMLFileDeserializer()->open($mappings_file);
        $deserializer->addHandler('mappings', function (array $mappings) use (&$context) {
            $context = $context->with('mappings', $mappings);
        });

        $deserializer->process();
        $this->log->info("Processed mappings file: {$mappings_file}");

        $this->session->setContext($context);
        return null;
    }
}
