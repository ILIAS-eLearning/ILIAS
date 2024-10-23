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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Chart\Bar\BarConfig;
use ILIAS\UI\Component\Chart\Bar\GroupConfig;

class ilPollResultsRenderer
{
    protected const SINGLE_BAR_WIDTH = 0.65;
    protected const SINGLE_BAR_COLOR = '#4C6586';
    protected const STACKED_BAR_WIDTH = 0.95;
    protected const STACKED_BAR_COLORS = [
        '#35485F',
        '#F06B05',
        '#374E1D',
        '#A18BB6',
        '#2C2C2C',
        '#3D9FAE',
        '#663D00',
        '#F75E82'
    ];

    protected int $ref_id;
    protected Refinery $refinery;
    protected DataFactory $data_factory;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilLanguage $lng;

    public function __construct(
        int $ref_id,
        Refinery $refinery,
        DataFactory $data_factory,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        ilLanguage $lng
    ) {
        $this->ref_id = $ref_id;
        $this->refinery = $refinery;
        $this->data_factory = $data_factory;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $lng;
    }

    public function render(
        ilTemplate $tpl,
        ilPollResultsHandler $results,
        int $presentation_mode
    ): void {
        if ($presentation_mode === ilObjPoll::SHOW_RESULTS_AS_STACKED_CHART) {
            $this->renderStackedChart($tpl, $results);
        } else {
            $this->renderBarChart($tpl, $results);
        }
    }

    protected function renderStackedChart(
        ilTemplate $tpl,
        ilPollResultsHandler $results
    ): void {
        $votes_label = $this->lng->txt('poll_chart_votes');
        $c_dimension = $this->data_factory->dimension()->cardinal();

        $dimensions = [];
        $data_values = [];
        $bar_configs = [];
        $tooltips = [];
        $color_index = 0;
        foreach ($results->getOrderedAnswerIds() as $id) {
            $label = $this->htmlSpecialCharsAsEntities($results->getAnswerText($id));
            $total_votes = $results->getAnswerTotal($id);
            $tooltip = $total_votes . ' (' . round($results->getAnswerPercentage($id)) . '%)';
            $bar_config = new BarConfig();

            $dimensions[$label] = $c_dimension;
            $data_values[$label] = $total_votes;
            $bar_configs[$label] = $bar_config->withColor(
                $this->data_factory->color(self::STACKED_BAR_COLORS[$color_index])
            )->withRelativeWidth(self::STACKED_BAR_WIDTH);
            $tooltips[$label] = $tooltip;

            $color_index = ($color_index + 1) >= count(self::STACKED_BAR_COLORS) ? 0 : ($color_index + 1);
        }

        $dimension_group = $this->data_factory->dimension()->group();
        $dataset = $this->data_factory->dataset(
            $dimensions,
            ['stacked' => $this->data_factory->dimension()->group(...array_keys($dimensions))]
        )->withPoint(
            $votes_label,
            $data_values
        )->withAlternativeInformation(
            $votes_label,
            $tooltips
        );

        $group_config = new GroupConfig();
        $group_config = $group_config->withStacked(true);
        $chart = $this->ui_factory->chart()->bar()->horizontal('', $dataset)
                                  ->withTitleVisible(false)
                                  ->withLegendVisible(true)
                                  ->withBarConfigs($bar_configs)
                                  ->withGroupConfigs(['stacked' => $group_config]);
        $tpl->setVariable('CHART', $this->ui_renderer->render($chart));
    }

    protected function renderBarChart(
        ilTemplate $tpl,
        ilPollResultsHandler $results
    ): void {
        $votes_label = $this->lng->txt('poll_chart_votes');
        $c_dimension = $this->data_factory->dimension()->cardinal();
        $dataset = $this->data_factory->dataset([$votes_label => $c_dimension]);

        foreach ($results->getOrderedAnswerIds() as $id) {
            $label = $this->htmlSpecialCharsAsEntities($results->getAnswerText($id));
            $total_votes = $results->getAnswerTotal($id);
            $tooltip = $total_votes . ' (' . round($results->getAnswerPercentage($id)) . '%)';
            $dataset = $dataset->withPoint($label, [$votes_label => $total_votes])
                               ->withAlternativeInformation($label, [$votes_label => $tooltip]);
        }

        $bar_config = new BarConfig();
        $bar_config = $bar_config->withColor($this->data_factory->color(self::SINGLE_BAR_COLOR))
                                 ->withRelativeWidth(self::SINGLE_BAR_WIDTH);
        $chart = $this->ui_factory->chart()->bar()->horizontal('', $dataset)
                                                  ->withTitleVisible(false)
                                                  ->withLegendVisible(false)
                                                  ->withBarConfigs([$votes_label => $bar_config]);
        $tpl->setVariable('CHART', $this->ui_renderer->render($chart));
    }

    protected function htmlSpecialCharsAsEntities(string $string): string
    {
        return $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(nl2br($string));
    }
}
