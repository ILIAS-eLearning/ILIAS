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

use ILIAS\Language\Language;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ImportStage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportSessionRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\StageResult;
use ILIAS\TestQuestionPool\RequestDataCollector;
use ilImport;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Final stage of the question pool import process. Imports the question pool object and all its questions and other
 * dependencies using `ilImport`. It will delegate the import to the `ilTestQuestionPoolImporter` class.
 */
class PersistStage implements ImportStage
{
    public function __construct(
        private readonly Language $lng,
        private readonly RequestDataCollector $request_data_collector,
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

    public function process(ImportContext $context, ServerRequestInterface $request): StageResult
    {
        $importer = new ilImport($this->request_data_collector->getRefId());
        $importer->importObject(
            null,
            $context->get('file_to_import'),
            basename($context->get('file_to_import')),
            'qpl',
            'components/ILIAS/TestQuestionPool',
            true,
        );

        // Context is updated by the QuestionPoolImporter so we need to reload it
        return StageResult::complete($this->session->getContext());
    }
}
