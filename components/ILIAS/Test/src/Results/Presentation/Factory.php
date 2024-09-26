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

namespace ILIAS\Test\Results\Presentation;

use ILIAS\Test\Results\Data\AttemptResult;
use ILIAS\Test\Results\Data\TestOverview;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControl;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;

class Factory
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected Refinery $refinery,
        protected DataFactory $data_factory,
        protected HTTPService $http,
        protected Language $lng
    ) {
    }

    public function getTestResultsOverviewlisting(
        TestOverview $overview
    ): DescriptiveListing {
        return $this->ui_factory->listing()->descriptive([]);
    }

    public function getAttemptResultsOverviewListing(
        AttemptResult $attempt_result
    ): DescriptiveListing {
        return $this->ui_factory->listing()->descriptive([]);
    }

    public function getAttemptResultsPresentationTable(
        AttemptResult $attempt_result,
        Settings $settings,
        string $title = ''
    ): AttemptResultsTable {
        return new AttemptResultsTable(
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http,
            $this->data_factory,
            $this->lng,
            $attempt_result,
            $settings,
            $title
        );
    }

    public function getAttemptResultsSettings(
        \ilObjTest $test_obj,
        bool $is_user_output
    ): Settings {
        $settings_result = $test_obj->getScoreSettings()->getResultDetailsSettings();

        $show_hidden_questions = false;
        $show_optional_questions = true;
        $show_best_solution = $is_user_output ?
            $settings_result->getShowSolutionListComparison() :
            (bool) \ilSession::get('tst_results_show_best_solutions');

        return new Settings(
            $test_obj->getId(),
            $show_hidden_questions,
            $show_optional_questions,
            $test_obj->getMainSettings()->getQuestionBehaviourSettings()->getQuestionHintsEnabled(),
            $show_best_solution,
            $settings_result->getShowSolutionFeedback(),
            $settings_result->getShowSolutionAnswersOnly(),
            $settings_result->getShowSolutionSuggested()
        );
    }
}
