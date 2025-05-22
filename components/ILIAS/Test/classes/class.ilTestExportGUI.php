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

use ILIAS\Test\Scoring\Manual\TestScoring;
use ILIAS\Test\ExportImport\DBRepository;
use ILIAS\Test\ExportImport\ResultsExportStakeholder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Test\ExportImport\Types as ExportImportTypes;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Export User Interface Class
 *
 * @author       Michael Jansen <mjansen@databay.de>
 * @author       Maximilian Becker <mbecker@databay.de>
 * @ilCtrl_Calls ilTestExportGUI: ilExportGUI
 */
class ilTestExportGUI extends ilExportGUI
{
    public function __construct(
        ilObjTestGUI $parent_gui,
        private readonly ilDBInterface $db,
        private readonly ilObjectDataCache $obj_cache,
        private readonly ilObjUser $user,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly IRSS $irss,
        private readonly ServerRequestInterface $request,
        private readonly DBRepository $export_repository,
        private readonly Filesystem $temp_file_system,
        private readonly ilTestParticipantAccessFilterFactory $participant_access_filter_factory,
        private readonly ilTestHTMLGenerator $html_generator
    ) {
        parent::__construct($parent_gui, null);
    }

    /**
     * Create test export file
     */
    public function createTestExportWithResults()
    {
        $this->ctrl->setParameterByClass(self::class, 'export_results', 1);
        $manager = $this->export_handler->manager()->handler();
        $export_info = $manager->getExportInfoWithObject(
            $this->obj,
            time()
        );
        $element = $manager->createExport(
            $this->il_user->getId(),
            $export_info,
            ''
        );

        $file_name = $element->getIRSSInfo()->getFileName();
        $this->temp_file_system->writeStream(
            $file_name,
            $this->irss->consume()->stream($element->getIRSSInfo()->getResourceId())->getStream()
        );
        $temp_stream = $this->temp_file_system->readStream($file_name);
        $rid = $this->irss->manage()->stream(
            $temp_stream,
            new ResultsExportStakeholder(),
            $element->getIRSSInfo()->getFileName()
        );

        $temp_stream->close();

        $this->temp_file_system->delete($file_name);

        $this->export_repository->store(
            $this->obj->getId(),
            ExportImportTypes::XML_WITH_RESULTS,
            $rid
        );
        $this->export_options->getById('expxml')->onDeleteFiles(
            $this->context,
            $this->export_handler->consumer()->file()->identifier()->collection()->withElement(
                $this->export_handler->consumer()->file()->identifier()->handler()->withIdentifier(
                    $element->getIRSSInfo()->getResourceIdSerialized()
                )
            )
        );

        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt("exp_file_created"),
            true
        );
        $this->ctrl->redirect($this, self::CMD_LIST_EXPORT_FILES);
    }

    public function createTestArchiveExport()
    {
        if ($this->access->checkAccess('write', '', $this->obj->getRefId())) {
            // prepare generation before contents are processed (for mathjax)
            $evaluation = new ilTestEvaluationFactory($this->db, $this->obj);
            $allActivesPasses = $evaluation->getAllActivesPasses();
            $participantData = new ilTestParticipantData($this->db, $this->lng);
            $participantData->setActiveIdsFilter(array_keys($allActivesPasses));
            $participantData->load($this->obj->getTestId());

            $archiveService = new ilTestArchiveService(
                $this->obj,
                $this->lng,
                $this->db,
                $this->user,
                $this->ui_factory,
                $this->ui_renderer,
                $this->irss,
                $this->request,
                $this->obj_cache,
                $this->participant_access_filter_factory,
                $this->html_generator
            );
            $archiveService->setParticipantData($participantData);
            $archiveService->archivePassesByActives($allActivesPasses);

            $test_id = $this->obj->getId();
            $test_ref = $this->obj->getRefId();
            $archive_exp = new ilTestArchiver(
                $this->lng,
                $this->db,
                $this->user,
                $this->ui_factory,
                $this->ui_renderer,
                $this->irss,
                $this->request,
                $this->obj_cache,
                $this->participant_access_filter_factory,
                $this->parent_gui->getTestObject()->getTestLogViewer(),
                $test_id,
                $test_ref
            );

            $scoring = new TestScoring($this->obj, $this->user, $this->db, $this->lng);
            $best_solution = $scoring->calculateBestSolutionForTest();

            $tmpFileName = ilFileUtils::ilTempnam();
            if (!is_dir($tmpFileName)) {
                ilFileUtils::makeDirParents($tmpFileName);
            }

            $archive_exp->handInTestBestSolution($best_solution);

            $archive_exp->updateTestArchive();
            $archive_exp->compressTestArchive();
        } else {
            $this->tpl->setOnScreenMessage('info', 'cannot_export_archive', true);
        }
        $this->ctrl->redirectByClass('iltestexportgui');
    }
}
