<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\ProgressMeter;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class Renderer extends AbstractComponentRenderer
{

    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Chart\ProgressMeter\ProgressMeter $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Chart\ProgressMeter\FixedSize) {
            /**
             * @var Component\Chart\ProgressMeter\FixedSize $component
             */
            return $this->renderFixedSize($component, $default_renderer);
        } elseif ($component instanceof Component\Chart\ProgressMeter\Mini) {
            /**
             * @var Component\Chart\ProgressMeter\Mini $component
             */
            return $this->renderMini($component, $default_renderer);
        } else {
            /**
             * @var Component\Chart\ProgressMeter\Standard $component
             */
            return $this->renderStandard($component, $default_renderer);
        }
    }

    /**
     * Render standard progressmeter
     *
     * @param Component\Chart\ProgressMeter\Standard $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderStandard(Component\Chart\ProgressMeter\Standard $component, RendererInterface $default_renderer)
    {
        $hasComparison = ($component->getComparison() != null && $component->getComparison() > 0);
        $tpl = $this->getTemplate("tpl.progressmeter.html", true, true);

        $tpl = $this->getDefaultGraphicByComponent($component, $tpl, $hasComparison);

        return $tpl->get();
    }

    /**
     * Render fixed size progressmeter
     *
     * @param Component\Chart\ProgressMeter\FixedSize $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderFixedSize(Component\Chart\ProgressMeter\FixedSize $component, RendererInterface $default_renderer)
    {
        $hasComparison = ($component->getComparison() != null && $component->getComparison() > 0);
        $tpl = $this->getTemplate("tpl.progressmeter.html", true, true);

        $tpl->setCurrentBlock('fixed');
        $tpl->setVariable('FIXED_CLASS', 'fixed-size');
        $tpl->parseCurrentBlock();

        $tpl = $this->getDefaultGraphicByComponent($component, $tpl, $hasComparison);

        return $tpl->get();
    }

    /**
     * Render mini progressmeter
     *
     * @param Component\Chart\ProgressMeter\Mini $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderMini(Component\Chart\ProgressMeter\Mini $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.progressmeter_mini.html", true, true);

        $main_percentage = $component->getMainValueAsPercent();

        // set progress bar color class
        $color_class = 'no-success';
        if ($this->getIsReached($main_percentage, $component->getRequiredAsPercent())) {
            $color_class = 'success';
        }
        $tpl->setVariable('COLOR_ONE_CLASS', $color_class);
        // set width for process bars
        $tpl->setVariable('BAR_ONE_WIDTH', (86.5 * ($main_percentage / 100)));
        // set marker position
        $needle_class = 'no-needle';
        if ($component->getRequired() != $component->getMaximum()) {
            $needle_class = '';
            $tpl->setVariable('ROTATE_ONE', $this->getMarkerPos($component->getRequiredAsPercent()));
        }
        $tpl->setVariable('NEEDLE_ONE_CLASS', $needle_class);

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    protected function getDefaultGraphicByComponent(
        Component\Chart\ProgressMeter\ProgressMeter $component,
        \ILIAS\UI\Implementation\Render\Template $tpl,
        $hasComparison = false
    ) {
        $main_percentage = $component->getMainValueAsPercent();

        if ($hasComparison) {
            // multicircle
            $tpl->setCurrentBlock('multicircle');
            // set first progress bar color class
            $color_one_class = 'no-success';
            if ($this->getIsReached($main_percentage, $component->getRequiredAsPercent())) {
                $color_one_class = 'success';
            }
            $tpl->setVariable('COLOR_ONE_CLASS', $color_one_class);
            // set width for first process bar
            $tpl->setVariable('BAR_ONE_WIDTH', $main_percentage);

            // set second progress bar color class
            $color_two_class = 'active';
            if (!$this->getIsValueSet($component->getMainValueAsPercent()) && $this->getIsValueSet($component->getComparison())) {
                $color_two_class = 'not-active';
            }
            $tpl->setVariable('COLOR_TWO_CLASS', $color_two_class);
            // set width for second process bar
            $tpl->setVariable('BAR_TWO_WIDTH', (88.8 * ($component->getComparisonAsPercent() / 100)));

            $tpl->parseCurrentBlock();
        } else {
            // monocircle
            $tpl->setCurrentBlock('monocircle');
            // set progress bar color class
            $color_class = 'no-success';
            if ($this->getIsReached($main_percentage, $component->getRequiredAsPercent())) {
                $color_class = 'success';
            }
            $tpl->setVariable('COLOR_ONE_CLASS', $color_class);
            // set width for process bars
            $tpl->setVariable('BAR_ONE_WIDTH', $main_percentage);

            $tpl->parseCurrentBlock();
        }

        // set visible values
        $tpl = $this->modifyVisibleValues($tpl, $component);

        // set marker position
        $needle_class = 'no-needle';
        if ($component->getRequired() != $component->getMaximum()) {
            $needle_class = '';
            $tpl->setVariable('ROTATE_ONE', (276 / 100 * $component->getRequiredAsPercent() - 138));
        }
        $tpl->setVariable('NEEDLE_ONE_CLASS', $needle_class);

        $tpl->parseCurrentBlock();

        return $tpl;
    }

    /**
     * Modify visible template variables
     *
     * @param \ILIAS\UI\Implementation\Render\Template $tpl
     * @param Component\Chart\ProgressMeter\ProgressMeter $component
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    protected function modifyVisibleValues(\ILIAS\UI\Implementation\Render\Template $tpl, Component\Component $component)
    {
        $tpl->setVariable("MAIN", $component->getMainValueAsPercent() . ' %');
        if ($component->getRequired() != $component->getMaximum()) {
            $tpl->setVariable("REQUIRED", $component->getRequiredAsPercent() . ' %');
        } else {
            $tpl->setVariable("REQUIRED", '');
        }
        $tpl->setVariable("TEXT_MAIN", htmlspecialchars($component->getMainText()));
        $tpl->setVariable("TEXT_REQUIRED", htmlspecialchars($component->getRequiredText()));
        return $tpl;
    }

    /**
     * get marker position by percent
     *
     * careful: marker position is no fixed positioning but
     *          a rotation value for marker box.
     *
     * @param int $percentage
     * @return float
     */
    protected function getMarkerPos($percentage)
    {
        return round((230 / 100 * ($percentage * 1)) - 115, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Test if value is not zero
     *
     * @param int|float $a_val
     * @return bool
     */
    protected function getIsValueSet($a_val)
    {
        return (isset($a_val) && $a_val > 0);
    }

    /**
     * Test if $a_val has reached $b_val
     *
     * This function may be used to check different
     * values with different has-to-reach values.
     *
     * @param int|float $a_val
     * @param int|float $b_val
     * @return bool
     */
    protected function getIsReached($a_val, $b_val)
    {
        return ($a_val >= $b_val);
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Chart\ProgressMeter\ProgressMeter::class];
    }
}
