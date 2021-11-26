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
use stdClass;

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
        return "";
    }

    protected function renderHorizontal(
        Bar\Horizontal $component,
        RendererInterface $default_renderer
    ) : string {
        $tpl = $this->getTemplate("tpl.bar_horizontal.html", true, true);

        $this->renderBasics($component, $tpl);

        $a11y_list = $this->getAccessibilityList($component, $component->getYLabels(), $component->getXLabels());
        $tpl->setVariable("LIST", $default_renderer->render($a11y_list));

        $chart_id = $component->getId();
        $options = json_encode($this->getParsedOptions($component));
        $data = json_encode($this->getParsedData($component));
        $x_labels = json_encode($this->reformatValueLabels($component->getXLabels()));
        $tooltips = json_encode($component->getToolTips());

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

        $a11y_list = $this->getAccessibilityList($component, $component->getXLabels(), $component->getYLabels());
        $tpl->setVariable("LIST", $default_renderer->render($a11y_list));

        $chart_id = $component->getId();
        $options = json_encode($this->getParsedOptions($component));
        $data = json_encode($this->getParsedData($component));
        $y_labels = json_encode($this->reformatValueLabels($component->getYLabels()));
        $tooltips = json_encode($component->getToolTips());

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

    protected function maybeRenderId(Component\JavaScriptBindable $component, Template $tpl)
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
        $tpl->setVariable("MIN_WIDTH", $component->getMinimumWidth());
        $tpl->setVariable("MIN_HEIGHT", $component->getMinimumHeight());
        if (!$component->isResponsive() && !empty($component->getWidth()) && !empty($component->getHeight())) {
            $tpl->setVariable("WIDTH", $component->getWidth());
            $tpl->setVariable("HEIGHT", $component->getHeight());
        }
    }

    protected function getAccessibilityList(
        Bar\Bar $component,
        array $item_labels,
        array $value_labels
    ) : Component\Listing\Descriptive {
        $ui_fac = $this->getUIFactory();

        $data = $component->getData();
        $lowest = $this->getLowestValueofData($component);
        $tooltips = $component->getToolTips();
        $list_items = [];
        $i = 0;
        foreach ($data as $dataset) {
            $ii = 0;
            $entries = [];
            foreach ($dataset["data"] as $value) {
                if (!empty($tooltips[$i])) {
                    // use custom tooltips if defined
                    $entries[] = $item_labels[$ii] . ": " . $tooltips[$i][$ii];
                } elseif (is_array($value)) {
                    // handle range values
                    $range = "";
                    foreach ($value as $v) {
                        $range .= $v . " - ";
                    }
                    $range = rtrim($range, " - ");
                    $entries[] = $item_labels[$ii] . ": " . $range;
                } elseif (is_null($value)) {
                    // handle null values
                    $undef = "-";
                    $entries[] = $item_labels[$ii] . ": " . $undef;
                } elseif (!empty($value_labels) && is_int($value) && !empty($value_labels[$value - $lowest])) {
                    // use custom value labels if defined
                    $entries[] = $item_labels[$ii] . ": " . $value_labels[$value - $lowest];
                } else {
                    // use numeric value for all other cases
                    $entries[] = $item_labels[$ii] . ": " . $value;
                }
                $ii++;
            }
            $list_items[$dataset["label"]] = $ui_fac->listing()->unordered($entries);
            $i++;
        }
        $list = $ui_fac->listing()->descriptive($list_items);

        return $list;
    }

    public function getLowestValueofData($component) : int
    {
        $min = 0;
        foreach ($component->getData() as $dataset) {
            foreach ($dataset["data"] as $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if ($min > $v) {
                            $min = floor($v);
                        }
                    }
                } elseif (!is_null($value) && $min > $value) {
                    $min = floor($value);
                }
            }
        }
        if ($component instanceof Bar\Horizontal) {
            $min = $component->getXAxis()["min"] ?? $min;
        } elseif ($component instanceof Bar\Vertical) {
            $min = $component->getYAxis()["min"] ?? $min;
        }

        return (int) $min;
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
        $options->responsive = $component->isResponsive();
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

        if (empty($component->getYAxes())) {
            $scales->y = new stdClass();
        } else {
            foreach ($component->getYAxes() as $axis_id => $axis) {
                $scales->{$axis_id} = new stdClass();
                foreach ($axis as $option => $value) {
                    $scales->{$axis_id}->$option = $value;
                }
            }
        }

        return $scales;
    }

    protected function getParsedOptionsForVertical(Bar\Bar $component) : stdClass
    {
        $scales = new stdClass();
        $scales->y = $component->getYAxis();

        if (empty($component->getXAxes())) {
            $scales->x = new stdClass();
        } else {
            foreach ($component->getXAxes() as $axis_id => $axis) {
                $scales->{$axis_id} = new stdClass();
                foreach ($axis as $option => $value) {
                    $scales->{$axis_id}->$option = $value;
                }
            }
        }

        return $scales;
    }

    protected function getParsedData(Bar\Bar $component) : stdClass
    {
        $data = new stdClass();
        $data->datasets = new stdClass();

        $user_data = $component->getData();
        $datasets = [];
        foreach ($user_data as $set) {
            $dataset = new stdClass();
            foreach ($set as $option => $value) {
                $dataset->$option = $value;
            }
            $datasets[] = $dataset;
        }
        $data->datasets = $datasets;

        if ($component instanceof Bar\Horizontal) {
            $data->labels = $component->getYLabels();
        } elseif ($component instanceof Bar\Vertical) {
            $data->labels = $component->getXLabels();
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
