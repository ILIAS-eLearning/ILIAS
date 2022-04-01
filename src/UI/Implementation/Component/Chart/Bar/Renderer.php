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

        $options = json_encode($this->getParsedOptions($component));
        $data = json_encode($this->getParsedData($component));
        $dimensions = $component->getDataset()->getDimensions();
        $x_labels = json_encode($this->reformatValueLabels($dimensions[key($dimensions)]->getLabels()));
        $tooltips = json_encode($component->getDataset()->getAlternativeInformation());

        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($options, $data, $x_labels, $tooltips) {
                return "il.UI.chart.bar.horizontal.init(
                    $id,
                    $options,
                    $data,
                    $x_labels,
                    $tooltips
                );";
            }
        );
        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);

        return $tpl->get();
    }

    protected function renderVertical(
        Bar\Vertical $component,
        RendererInterface $default_renderer
    ) : string {
        $tpl = $this->getTemplate("tpl.bar_vertical.html", true, true);

        $this->renderBasics($component, $tpl);

        $options = json_encode($this->getParsedOptions($component));
        $data = json_encode($this->getParsedData($component));
        $dimensions = $component->getDataset()->getDimensions();
        $y_labels = json_encode($this->reformatValueLabels($dimensions[key($dimensions)]->getLabels()));
        $tooltips = json_encode($component->getDataset()->getAlternativeInformation());

        $a11y_list = $this->getAccessibilityList($component);
        $tpl->setVariable("LIST", $default_renderer->render($a11y_list));

        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($options, $data, $y_labels, $tooltips) {
                return "il.UI.chart.bar.vertical.init(
                    $id,
                    $options,
                    $data,
                    $y_labels,
                    $tooltips
                );";
            }
        );
        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);

        return $tpl->get();
    }

    protected function renderBasics(Bar\Bar $component, Template $tpl) : void
    {
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
        $tooltips_per_dimension = $component->getDataset()->getAlternativeInformationPerDimension();
        $dimensions = $component->getDataset()->getDimensions();
        $value_labels = $dimensions[key($dimensions)]->getLabels();
        $lowest = $this->getLowestValueOfChart($component);
        $list_items = [];

        foreach ($points_per_dimension as $dimension_name => $item_points) {
            $entries = [];
            foreach ($item_points as $messeaurement_item_label => $point) {
                if (isset($tooltips_per_dimension[$dimension_name][$messeaurement_item_label])) {
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
        $min = null;
        $new_min = 0;
        foreach ($component->getDataset()->getDimensions() as $dimension_name => $dimension) {
            $new_min = floor($component->getDataset()->getMinValueForDimension($dimension_name));
            if (is_null($min) || $new_min < $min) {
                $min = $new_min;
            }
        }

        if ($component instanceof Bar\Horizontal) {
            $min = $component->getXAxis()->getMinValue() ?? $min;
        } elseif ($component instanceof Bar\Vertical) {
            $min = $component->getYAxis()->getMinValue() ?? $min;
        }

        return (int) $min;
    }

    public function getHighestValueOfChart(Bar\Bar $component) : int
    {
        $max = null;
        $new_max = 0;
        foreach ($component->getDataset()->getDimensions() as $dimension_name => $dimension) {
            $new_max = ceil($component->getDataset()->getMaxValueForDimension($dimension_name));
            if (is_null($max) || $new_max > $max) {
                $max = $new_max;
            }
        }

        if ($component instanceof Bar\Horizontal) {
            $max = $component->getXAxis()->getMaxValue() ?? $max;
        } elseif ($component instanceof Bar\Vertical) {
            $max = $component->getYAxis()->getMaxValue() ?? $max;
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
        $scales->y = new stdClass();
        $x_axis = $component->getXAxis();
        $scales->x = new stdClass();
        $scales->x->axis = $x_axis->getAbbreviation();
        $scales->x->type = $x_axis->getType();
        $scales->x->display = $x_axis->isDisplayed();
        $scales->x->position = $x_axis->getPosition();
        $scales->x->beginAtZero = $x_axis->isBeginAtZero();
        $scales->x->ticks = new stdClass();
        $scales->x->ticks->callback = null;
        $scales->x->ticks->stepSize = $x_axis->getStepSize();
        if ($x_axis->getMinValue()) {
            $scales->x->min = $x_axis->getMinValue();
        }
        if ($x_axis->getMaxValue()) {
            $scales->x->max = $x_axis->getMaxValue();
        }

        // hide pseudo y axes
        $dimension_scales = $component->getDataset()->getDimensions();
        foreach ($dimension_scales as $scale_id) {
            $scales->{get_class($scale_id)} = new stdClass();
            $scales->{get_class($scale_id)}->axis = "y";
            $scales->{get_class($scale_id)}->display = false;
        }

        return $scales;
    }

    protected function getParsedOptionsForVertical(Bar\Bar $component) : stdClass
    {
        $scales = new stdClass();
        $scales->x = new stdClass();
        $y_axis = $component->getYAxis();
        $scales->y = new stdClass();
        $scales->y->axis = $y_axis->getAbbreviation();
        $scales->y->type = $y_axis->getType();
        $scales->y->display = $y_axis->isDisplayed();
        $scales->y->position = $y_axis->getPosition();
        $scales->y->beginAtZero = $y_axis->isBeginAtZero();
        $scales->y->ticks = new stdClass();
        $scales->y->ticks->callback = null;
        $scales->y->ticks->stepSize = $y_axis->getStepSize();
        if ($y_axis->getMinValue()) {
            $scales->y->min = $y_axis->getMinValue();
        }
        if ($y_axis->getMaxValue()) {
            $scales->y->max = $y_axis->getMaxValue();
        }

        // hide pseudo x axes
        $dimension_scales = $component->getDataset()->getDimensions();
        foreach ($dimension_scales as $scale_id) {
            $scales->{get_class($scale_id)} = new stdClass();
            $scales->{get_class($scale_id)}->axis = "x";
            $scales->{get_class($scale_id)}->display = false;
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
            $dataset = (object) $set;
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
        $bar_configs = $component->getBarConfigs();
        $data = [];

        foreach ($points_per_dimension as $dimension_name => $item_points) {
            $data[$dimension_name]["label"] = $dimension_name;
            if (isset($bar_configs[$dimension_name]) && $bar_configs[$dimension_name]->getColor()) {
                $data[$dimension_name]["backgroundColor"] = $bar_configs[$dimension_name]->getColor()->asHex();
            }
            if (isset($bar_configs[$dimension_name]) && $bar_configs[$dimension_name]->getRelativeWidth()) {
                $data[$dimension_name]["barPercentage"] = $bar_configs[$dimension_name]->getRelativeWidth();
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
                $data[$dimension_name]["yAxisID"] = get_class($dimensions[$dimension_name]);
            } elseif ($component instanceof Bar\Vertical) {
                foreach ($item_points as $x_point => $y_point) {
                    $datasets = new stdClass();
                    $datasets->data = new stdClass();
                    $datasets->data->x = $x_point;
                    $datasets->data->y = $y_point;
                    $points_as_objects[] = $datasets->data;
                }
                $data[$dimension_name]["data"] = $points_as_objects;
                $data[$dimension_name]["xAxisID"] = get_class($dimensions[$dimension_name]);
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
