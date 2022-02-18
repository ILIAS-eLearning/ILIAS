<?php declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\UI\Implementation\Component\Chart\Bar;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Chart\Bar;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\Data\Dimension\Dimension;
use stdClass;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        /**
         * @var Bar\Bar $component
         */
        $this->checkComponent($component);

        if ($component instanceof Bar\Horizontal) {
            /**
             * @var Bar\Horizontal $component
             */
            return $this->renderHorizontal($component, $default_renderer);
        } elseif ($component instanceof Bar\Vertical) {
            /**
             * @var Bar\Vertical $component
             */
            return $this->renderVertical($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function renderHorizontal(
        Bar\Horizontal $component,
        RendererInterface $default_renderer
    ) : string {
        $tpl = $this->getTemplate("tpl.bar_horizontal.html", true, true);

        $this->renderBasics($component, $tpl);

        $a11y_list = $this->getAccessibilityList($component);
        $tpl->setVariable("LIST", $default_renderer->render($a11y_list));

        $chart_id = $component->getId();
        $options = json_encode($this->getParsedOptions($component));
        $data = json_encode($this->getParsedData($component));
        $dimensions = $component->getDataset()->getDimensions();
        $x_labels = json_encode($this->reformatValueLabels($dimensions[key($dimensions)]->getLabels()));
        $tooltips = json_encode($component->getDataset()->getToolTips());

        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($chart_id, $options, $data, $x_labels, $tooltips) {
                return "il.UI.chart.bar.horizontal.init(
                    '$chart_id',
                    $options,
                    $data,
                    $x_labels,
                    $tooltips
                );";
            }
        );
        $this->maybeRenderId($component, $tpl);

        return $tpl->get();
    }

    protected function renderVertical(
        Bar\Vertical $component,
        RendererInterface $default_renderer
    ) : string {
        $tpl = $this->getTemplate("tpl.bar_vertical.html", true, true);

        $this->renderBasics($component, $tpl);

        $chart_id = $component->getId();
        $options = json_encode($this->getParsedOptions($component));
        $data = json_encode($this->getParsedData($component));
        $dimensions = $component->getDataset()->getDimensions();
        $y_labels = json_encode($this->reformatValueLabels($dimensions[key($dimensions)]->getLabels()));
        $tooltips = json_encode($component->getDataset()->getToolTips());

        $a11y_list = $this->getAccessibilityList($component);
        $tpl->setVariable("LIST", $default_renderer->render($a11y_list));

        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($chart_id, $options, $data, $y_labels, $tooltips) {
                return "il.UI.chart.bar.vertical.init(
                    '$chart_id',
                    $options,
                    $data,
                    $y_labels,
                    $tooltips
                );";
            }
        );
        $this->maybeRenderId($component, $tpl);

        return $tpl->get();
    }

    protected function maybeRenderId(Component\JavaScriptBindable $component, Template $tpl) : void
    {
        $id = $this->bindJavaScript($component);
        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }
    }

    protected function renderBasics(Bar\Bar $component, Template $tpl) : void
    {
        $tpl->setVariable("CHART_ID", $component->getId());
        $tpl->setVariable("TITLE", $component->getTitle());
        $height = "";
        if ($component instanceof Bar\Horizontal) {
            $height = $this->determineHeightForHorizontal($component);
        } elseif ($component instanceof Bar\Vertical) {
            $height = $this->determineHeightForVertical($component);
        }
        $tpl->setVariable("HEIGHT", $height);
    }

    protected function determineHeightForHorizontal(Bar\Bar $component) : string
    {
        $min_height = 300;
        $max_height = 900;
        $item_count = count($component->getDataset()->getPoints());
        $height = $min_height + ($item_count - 2) * 50;
        if ($height > $max_height) {
            $height = $max_height;
        }

        return $height . "px";
    }

    protected function determineHeightForVertical(Bar\Bar $component) : string
    {
        $min_height = 300;
        $max_height = 900;
        $data_max = $this->getHighestValueOfChart($component) - $this->getLowestValueOfChart($component);
        $height = $min_height + ($data_max / 10) * 50;
        if ($height > $max_height) {
            $height = $max_height;
        }

        return $height . "px";
    }

    protected function getAccessibilityList(
        Bar\Bar $component
    ) : Component\Listing\Descriptive {
        $ui_fac = $this->getUIFactory();

        $points_per_dimension = $component->getDataset()->getPointsPerDimension();
        $tooltips_per_dimension = $component->getDataset()->getTooltipsPerDimension();
        $dimensions = $component->getDataset()->getDimensions();
        $value_labels = $dimensions[key($dimensions)]->getLabels();
        $lowest = $this->getLowestValueOfChart($component);
        $list_items = [];

        foreach ($points_per_dimension as $dimension_name => $item_points) {
            $entries = [];
            foreach ($item_points as $messeaurement_item_label => $point) {
                if ($tooltips_per_dimension[$dimension_name][$messeaurement_item_label]) {
                    // use custom tooltips if defined
                    $entries[] = $messeaurement_item_label . ": " . $tooltips_per_dimension[$dimension_name][$messeaurement_item_label];
                } elseif (is_array($point)) {
                    // handle range values
                    $range = "";
                    foreach ($point as $p) {
                        $range .= $p . " - ";
                    }
                    $range = rtrim($range, " -");
                    $entries[] = $messeaurement_item_label . ": " . $range;
                } elseif (is_null($point)) {
                    // handle null values
                    $entries[] = $messeaurement_item_label . ": -";
                } elseif (!empty($value_labels) && is_int($point) && !empty($value_labels[$point - $lowest])) {
                    // use custom value labels if defined
                    $entries[] = $messeaurement_item_label . ": " . $value_labels[$point - $lowest];
                } else {
                    // use numeric value for all other cases
                    $entries[] = $messeaurement_item_label . ": " . $point;
                }
            }
            $list_items[$dimension_name] = $ui_fac->listing()->unordered($entries);
        }

        $list = $ui_fac->listing()->descriptive($list_items);

        return $list;
    }

    public function getLowestValueOfChart(Bar\Bar $component) : int
    {
        $min = floor($component->getDataset()->getMinValue());

        if ($component instanceof Bar\Horizontal) {
            $min = $component->getXAxis()["min"] ?? $min;
        } elseif ($component instanceof Bar\Vertical) {
            $min = $component->getYAxis()["min"] ?? $min;
        }

        return (int) $min;
    }

    public function getHighestValueOfChart(Bar\Bar $component) : int
    {
        $max = ceil($component->getDataset()->getMaxValue());

        if ($component instanceof Bar\Horizontal) {
            $max = $component->getXAxis()["max"] ?? $max;
        } elseif ($component instanceof Bar\Vertical) {
            $max = $component->getYAxis()["max"] ?? $max;
        }

        return (int) $max;
    }

    protected function reformatValueLabels(array $labels) : array
    {
        $index = 0;
        $new_labels = [];
        foreach ($labels as $label) {
            $new_labels[$index] = $label;
            $index++;
        }

        return $new_labels;
    }

    protected function getParsedOptions(Bar\Bar $component) : stdClass
    {
        $options = new stdClass();
        $options->indexAxis = $component->getIndexAxis();
        $options->responsive = true;
        $options->maintainAspectRatio = false;
        $options->plugins = new stdClass();
        $options->plugins->legend = new stdClass();
        $options->plugins->legend->display = $component->isLegendVisible();
        $options->plugins->legend->position = $component->getLegendPosition();
        $options->plugins->tooltip = new stdClass();
        $options->plugins->tooltip->enabled = $component->isTooltipsVisible();
        $options->plugins->tooltip->callbacks = new stdClass();
        $options->plugins->title = new stdClass();
        $options->plugins->title->display = $component->isTitleVisible();
        $options->plugins->title->text = $component->getTitle();

        if ($component instanceof Bar\Horizontal) {
            $options->scales = $this->getParsedOptionsForHorizontal($component);
        } elseif ($component instanceof Bar\Vertical) {
            $options->scales = $this->getParsedOptionsForVertical($component);
        }

        return $options;
    }

    protected function getParsedOptionsForHorizontal(Bar\Bar $component) : stdClass
    {
        $scales = new stdClass();
        $scales->x = $component->getXAxis();
        $scales->y = new stdClass();

        // hide pseudo y axes
        $dimension_scales = Dimension::getAllTypes();
        foreach ($dimension_scales as $scale_id) {
            $scales->{$scale_id} = new stdClass();
            $scales->{$scale_id}->axis = Bar\Bar::AXIS_Y;
            $scales->{$scale_id}->display = false;
        }

        return $scales;
    }

    protected function getParsedOptionsForVertical(Bar\Bar $component) : stdClass
    {
        $scales = new stdClass();
        $scales->y = $component->getYAxis();
        $scales->x = new stdClass();

        // hide pseudo x axes
        $dimension_scales = Dimension::getAllTypes();
        foreach ($dimension_scales as $scale_id) {
            $scales->{$scale_id} = new stdClass();
            $scales->{$scale_id}->axis = Bar\Bar::AXIS_X;
            $scales->{$scale_id}->display = false;
        }

        return $scales;
    }

    protected function getParsedData(Bar\Bar $component) : stdClass
    {
        $data = new stdClass();
        $data->datasets = new stdClass();

        $user_data = $this->getUserData($component);
        $datasets = [];
        foreach ($user_data as $set) {
            $dataset = new stdClass();
            foreach ($set as $option => $value) {
                $dataset->$option = $value;
            }
            $datasets[] = $dataset;
        }
        $data->datasets = $datasets;
        $data->labels = array_keys($component->getDataset()->getPoints());

        return $data;
    }

    protected function getUserData(Bar\Bar $component) : array
    {
        $points_per_dimension = $component->getDataset()->getPointsPerDimension();
        $dimensions = $component->getDataset()->getDimensions();
        $bars = $component->getBars();
        $data = [];

        foreach ($points_per_dimension as $dimension_name => $item_points) {
            $data[$dimension_name]["label"] = $dimension_name;
            if ($bars[$dimension_name]->getColor()) {
                $data[$dimension_name]["backgroundColor"] = $bars[$dimension_name]->getColor();
            }
            if ($bars[$dimension_name]->getSize()) {
                $data[$dimension_name]["barPercentage"] = $bars[$dimension_name]->getSize();
            }

            $points_as_objects = [];
            if ($component instanceof Bar\Horizontal) {
                foreach ($item_points as $y_point => $x_point) {
                    $datasets = new stdClass();
                    $datasets->data = new stdClass();
                    $datasets->data->y = $y_point;
                    $datasets->data->x = $x_point;
                    $points_as_objects[] = $datasets->data;
                }
                $data[$dimension_name]["data"] = $points_as_objects;
                $data[$dimension_name]["yAxisID"] = $dimensions[$dimension_name]->getType();
            } elseif ($component instanceof Bar\Vertical) {
                foreach ($item_points as $x_point => $y_point) {
                    $datasets = new stdClass();
                    $datasets->data = new stdClass();
                    $datasets->data->x = $x_point;
                    $datasets->data->y = $y_point;
                    $points_as_objects[] = $datasets->data;
                }
                $data[$dimension_name]["data"] = $points_as_objects;
                $data[$dimension_name]["xAxisID"] = $dimensions[$dimension_name]->getType();
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry) : void
    {
        parent::registerResources($registry);
        $registry->register('./node_modules/chart.js/dist/chart.min.js');
        $registry->register('./src/UI/templates/js/Chart/Bar/dist/bar.js');
    }

    protected function getComponentInterfaceName() : array
    {
        return [Bar\Bar::class];
    }
}
