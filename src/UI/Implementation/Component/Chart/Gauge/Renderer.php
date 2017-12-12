<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\Gauge;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Chart\Gauge
 */
class Renderer extends AbstractComponentRenderer
{

    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Chart\Gauge\Gauge $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Chart\Gauge\FixedSize) {
            /**
             * @var Component\Chart\Gauge\FixedSize $component
             */
            return $this->renderFixedSize($component, $default_renderer);

        } elseif ($component instanceof Component\Chart\Gauge\Mini) {
            /**
             * @var Component\Chart\Gauge\Mini $component
             */
            return $this->renderMini($component, $default_renderer);

        } else {
            /**
             * @var Component\Chart\Gauge\Standard $component
             */
            return $this->renderStandard($component, $default_renderer);
        }

    }

    /**
     * Render standard gauge
     *
     * @param Component\Chart\Gauge\Standard $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderStandard(Component\Chart\Gauge\Standard $component, RendererInterface $default_renderer)
    {
        if ($component->hasComparisonValue()) {
            $tpl = $this->getTemplate("tpl.gauge_two_bar.html", true, true);
        } else {
            $tpl = $this->getTemplate("tpl.gauge_one_bar.html", true, true);
        }

        // set "responsive class" false
        $tpl->touchBlock('responsive');

        // set visible values
        $tpl = $this->modifyVisibleValues($tpl, $component);

        // set skew and rotation for process bars
        $tpl = $this->modifyProgressBar($tpl, $component->getMainValueAsPercent(), 'MAIN');
        if ($component->hasComparisonValue()) {
            $tpl = $this->modifyProgressBar($tpl, $component->getComparisionAsPercent(), 'COMPARE');
        }

        // set progress bar color class
        $tpl = $this->modifyProgressBarClasses($tpl, $component);

        // set marker position
        if($component->getRequired() != $component->getMaximum()) {
            $tpl->setVariable("MARKER_POS", $this->getMarkerPos($component->getRequiredAsPercent()));
        } else {
            $tpl->setVariable("MARKER_POS",'180');
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Render fixed size gauge
     *
     * @param Component\Chart\Gauge\FixedSize $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderFixedSize(Component\Chart\Gauge\FixedSize $component, RendererInterface $default_renderer)
    {
        if ($component->hasComparisonValue()) {
            $tpl = $this->getTemplate("tpl.gauge_two_bar.html", true, true);
        } else {
            $tpl = $this->getTemplate("tpl.gauge_one_bar.html", true, true);
        }

        // set "responsive class" false
        $tpl->touchBlock('fixed-size');

        // set visible values
        $tpl = $this->modifyVisibleValues($tpl, $component);

        // set skew and rotation for process bars
        $tpl = $this->modifyProgressBar($tpl, $component->getMainValueAsPercent(), 'MAIN');
        if ($component->hasComparisonValue()) {
            $tpl = $this->modifyProgressBar($tpl, $component->getComparisionAsPercent(), 'COMPARE');
        }

        // set progress bar color class
        $tpl = $this->modifyProgressBarClasses($tpl, $component);

        // set marker position
        if($component->getRequired() != $component->getMaximum()) {
            $tpl->setVariable("MARKER_POS", $this->getMarkerPos($component->getRequiredAsPercent()));
        } else {
            $tpl->setVariable("MARKER_POS",'180');
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Render mini gauge
     *
     * @param Component\Chart\Gauge\Mini $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderMini(Component\Chart\Gauge\Mini $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.gauge_mini.html", true, true);

        // set skew and rotation for process bars
        $tpl = $this->modifyProgressBar($tpl, $component->getMainValueAsPercent(), 'MAIN');

        // set progress bar color class
        $tpl = $this->modifyProgressBarClasses($tpl, $component);

        // set marker position
        if($component->getRequired() != $component->getMaximum()) {
            $tpl->setVariable("MARKER_POS", $this->getMarkerPos($component->getRequiredAsPercent()));
        } else {
            $tpl->setVariable("MARKER_POS",'0');
            $tpl->touchBlock('marker-hidden');
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Modify visible template variables
     *
     * @param \ILIAS\UI\Implementation\Render\Template $tpl
     * @param Component\Component $component
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    protected function modifyVisibleValues(\ILIAS\UI\Implementation\Render\Template $tpl, Component\Component $component)
    {
        $tpl->setVariable("MAIN", $component->getMainValueAsPercent().' %');
        if($component->getRequired() != $component->getMaximum()) {
            $tpl->setVariable("REQUIRED", $component->getRequiredAsPercent().' %');
        } else {
            $tpl->setVariable("REQUIRED", '');
        }
        if ($component->hasComparisonValue()) {
            $tpl->setVariable("COMPARE", $component->getComparisionAsPercent().' %');
        }
        $tpl->setVariable("TEXT_MAIN", htmlspecialchars($component->getMainText()));
        $tpl->setVariable("TEXT_REQUIRED", htmlspecialchars($component->getRequiredText()));
        return $tpl;
    }

    /**
     * Modify the template skew and rotation variables for a specific progress bar, identified by its prefix
     *
     * @param \ILIAS\UI\Implementation\Render\Template $tpl
     * @param float $value   Percantage value to render bar.
     * @param string $prefix Prefix to identify bar in template.
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    protected function modifyProgressBar(\ILIAS\UI\Implementation\Render\Template $tpl, $value, $prefix)
    {
        $skew_value = $this->getSkew($value);
        $tpl->setVariable("SKEW_".$prefix, $skew_value);
        $rotation_value = $this->getRotation($skew_value);
        $tpl->setVariable("ROTATION_".$prefix."1", $rotation_value[0]);
        $tpl->setVariable("ROTATION_".$prefix."2", $rotation_value[1]);
        $tpl->setVariable("ROTATION_".$prefix."3", $rotation_value[2]);
        return $tpl;
    }

    /**
     * Modify the template variables for the progress bar classes, used for colors
     *
     * @param \ILIAS\UI\Implementation\Render\Template $tpl
     * @param Component\Chart\Gauge\Standard $component
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    protected function modifyProgressBarClasses(\ILIAS\UI\Implementation\Render\Template $tpl, Component\Component $component)
    {
        if ($this->getIsValueSet($component->getMainValueAsPercent())) {
            if($this->getIsReached($component->getMainValueAsPercent(), $component->getRequiredAsPercent())) {
                $tpl->touchBlock('outer-bar-success');
            } else {
                $tpl->touchBlock('outer-bar-no-success');
            }
        } else {
            if ($component->hasComparisonValue()) {
                $tpl->touchBlock('inner-bar-yellow'); // @Todo find better wording for css class name
            }
        }
        return $tpl;
    }

    /**
     * get skew by percent
     *
     * @param int $percentage
     * @return float
     */
    protected function getSkew($percentage)
    {
        return (((90 - (3.6 * ($percentage - 100))) - 90) / 4 + ($percentage * 0.1323));
    }

    /**
     * get rotation by skew
     *
     * @param float $skew
     * @return array
     */
    protected function getRotation($skew)
    {
        $rotation = array();
        $rotation[0] = (-25 + ((90 - $skew) * 0));
        $rotation[1] = (-25 + ((90 - $skew-1) * 1));
        $rotation[2] = (-25 + ((90 - $skew-1) * 2));
        return $rotation;
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
        return [Component\Chart\Gauge\Gauge::class];
    }
}