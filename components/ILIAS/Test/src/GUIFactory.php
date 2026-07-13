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

namespace ILIAS\Test;

use Pimple\Container as PimpleContainer;
use ILIAS\Test\Scoring\Manual\ConsecutiveScoringGUI;
use ILIAS\Test\Scoring\Manual\TestScoring;
use ILIAS\Test\Scoring\Manual\ConsecutiveScoring;
use ILIAS\Test\Scoring\Manual\ConsecutiveScoringURLs;
use ILIAS\Test\Scoring\Manual\PositionsFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Factory as DataFactory;

class GUIFactory
{
    private array $definitions;
    private array $internal;

    public function get(string $gui_name, \ilObjTest $test_obj)
    {
        if (! array_key_exists($gui_name, $this->definitions)) {
            throw new \InvalidArgumentException('No such GUI: ' . $gui_name);
        }
        return $this->definitions[$gui_name]($test_obj);
    }

    public function __construct(
        private PimpleContainer $global_dic,
        private TestDIC $test_dic
    ) {
        $this->definitions['ILIAS\Test\Scoring\Manual\ConsecutiveScoringGUI'] = fn(\ilObjTest $test_obj): ConsecutiveScoringGUI =>
             new ConsecutiveScoringGUI(
                 $this->global_dic['ilCtrl'],
                 $this->global_dic['tpl'],
                 $this->global_dic['ilTabs'],
                 $this->global_dic['lng'],
                 $test_obj,
                 $this->internal['test.access']($test_obj),
                 $this->global_dic['ui.factory'],
                 $this->global_dic['ui.renderer'],
                 $this->global_dic['refinery'],
                 $this->global_dic->http()->request(),
                 $this->test_dic['response_handler'],
                 $this->internal['manscoring.consecutive']($test_obj),
                 $this->internal['urlbuilder.manscoring']($test_obj),
                 $this->global_dic->uiService()->filter(),
             );

        $this->internal['test.access'] = static fn(\ilObjTest $test_obj): \ilTestAccess =>
            new \ilTestAccess($test_obj->getRefId());

        $this->internal['manscoring.consecutive'] = fn(\ilObjTest $test_obj): ConsecutiveScoring =>
                new ConsecutiveScoring(
                    $this->internal['manscoring.positionsfactory']($test_obj)->get(),
                    $test_obj,
                    $this->test_dic['shuffler'],
                    $this->test_dic['logging.logger'],
                    $this->internal['manscoring.testscoring']($test_obj),
                    $this->test_dic['scoring.manual.done_helper'],
                    $this->global_dic['ilUser'],
                    $this->internal['test.access']($test_obj),
                    $this->test_dic['participant.repository'],
                    $this->global_dic['lng'],
                );

        $this->internal['manscoring.positionsfactory'] = fn(\ilObjTest $test_obj): PositionsFactory =>
            new PositionsFactory(
                $test_obj,
                $this->test_dic['question.general_properties.repository']
            );

        $this->internal['manscoring.testscoring'] = fn(\ilObjTest $test_obj): TestScoring =>
                new TestScoring(
                    $test_obj,
                    $this->global_dic['ilUser'],
                    $this->global_dic['ilDB'],
                    $this->test_dic['results.data.repository']
                );

        $this->internal['urlbuilder.current'] = fn(): URLBuilder =>
            new URLBuilder(
                (new DataFactory())->uri($this->global_dic->http()->request()->getUri()->__toString())
            );

        $this->internal['urlbuilder.manscoring'] = fn(\ilObjTest $test_obj): ConsecutiveScoringURLs =>
            new ConsecutiveScoringURLs(
                $this->internal['urlbuilder.current'](),
                ['tams_' . $test_obj->getRefId()],
                $this->global_dic['refinery'],
                $this->global_dic['http']->wrapper()->query(),
                $this->global_dic['ilCtrl'],
            );
    }
}
