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

namespace ILIAS\Test\ExportImport;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Logging\TestLogger;

class Factory
{
    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly TestLogger $logger,
        private readonly \ilTree $tree,
        private readonly \ilComponentRepository $component_repository,
        private readonly \ilComponentFactory $component_factory,
        private readonly GeneralQuestionPropertiesRepository $questionrepository
    ) {
    }

    public function getExporter(
        \ilObjTest $test_obj,
        string $export_type = 'xml'
    ): ExportAsAttachment|ExportFixedQuestionSet|ExportRandomQuestionSet {
        switch ($export_type) {
            case 'scored_test_run':
                return (new ResultsExportExcel($this->lng, $this->object, $filterby, $filtertext, $passedonly, true))
                    ->withResultsPage()
                    ->withUserPages();

            case 'all_test_runs':
                return (new ResultsExportExcel($this->lng, $this->object, $filterby, $filtertext, $passedonly, false))
                    ->withResultsPage()
                    ->withUserPages();

            case 'all_test_runs_a':

                return (new ResultsExportExcel($this->lng, $this->object, ilTestEvaluationData::FILTER_BY_NONE, '', false, true))
                    ->withAggregatedResultsPage();

            case 'certificate':
                $this->exportCertificateArchive();
                break;

            default:
                foreach ($this->component_factory->getActivePluginsInSlot('texp') as $plugin) {
                    if ($plugin->getFormat() === $export_type) {
                        $plugin->setTest($test_obj);
                        return $plugin;
                    }
                }
                if ($test_obj->isFixedTest()) {
                    return new ExportFixedQuestionSet($test_obj, $export_type);
                }
                return new ExportRandomQuestionSet(
                    $test_obj,
                    $this->lng,
                    $this->logger,
                    $this->tree,
                    $this->component_repository,
                    $this->questionrepository,
                    $export_type
                );
        }
    }
}
