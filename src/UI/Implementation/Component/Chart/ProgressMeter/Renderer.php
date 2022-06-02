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
 *********************************************************************/
 
namespace ILIAS\UI\Implementation\Component\Chart\ProgressMeter;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        /**
         * @var Component\Chart\ProgressMeter\ProgressMeter $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Chart\ProgressMeter\FixedSize) {
            /**
             * @var Component\Chart\ProgressMeter\FixedSize $component
             */
            return $this->renderFixedSize($component);
        } elseif ($component instanceof Mini) {
            /**
             * @var Mini $component
             */
            return $this->renderMini($component);
        } else {
            /**
             * @var Component\Chart\ProgressMeter\Standard $component
             */
            return $this->renderStandard($component);
        }
    }

    /**
     * Render standard progressmeter
     */
    protected function renderStandard(Component\Chart\ProgressMeter\Standard $component) : string
    {
        $hasComparison = ($component->getComparison() != null && $component->getComparison() > 0);
        $tpl = $this->getTemplate("tpl.progressmeter.html", true, true);

        $tpl = $this->getDefaultGraphicByComponent($component, $tpl, $hasComparison);

        return $tpl->get();
    }

    /**
     * Render fixed size progressmeter
     */
    protected function renderFixedSize(Component\Chart\ProgressMeter\FixedSize $component) : string
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
     */
    protected function renderMini(Mini $component) : string
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
        Template $tpl,
        $hasComparison = false
    ) : Template {
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
     */
    protected function modifyVisibleValues(Template $tpl, Component\Component $component) : Template
    {
        $tpl->setVariable("MAIN", $component->getMainValueAsPercent() . ' %');
        if ($component->getRequired() != $component->getMaximum()) {
            $tpl->setVariable("REQUIRED", $component->getRequiredAsPercent() . ' %');
        } else {
            $tpl->setVariable("REQUIRED", '');
        }

        $main_text = '';
        if (!is_null($component->getMainText())) {
            $main_text = $component->getMainText();
        }

        $required_text = '';
        if (!is_null($component->getRequiredText())) {
            $required_text = $component->getRequiredText();
        }
        $tpl->setVariable("TEXT_MAIN", htmlspecialchars($main_text));
        $tpl->setVariable("TEXT_REQUIRED", htmlspecialchars($required_text));
        return $tpl;
    }

    /**
     * get marker position by percent
     *
     * careful: marker position is no fixed positioning but
     *          a rotation value for marker box.
     */
    protected function getMarkerPos(int $percentage) : float
    {
        return round((230 / 100 * ($percentage * 1)) - 115, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Test if value is not zero
     *
     * @param int|float $val
     */
    protected function getIsValueSet($val) : bool
    {
        return (isset($val) && $val > 0);
    }

    /**
     * Test if $a_val has reached $b_val
     *
     * This function may be used to check different
     * values with different has-to-reach values.
     *
     * @param int|float $a_val
     * @param int|float $b_val
     */
    protected function getIsReached($a_val, $b_val) : bool
    {
        return ($a_val >= $b_val);
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() : array
    {
        return [Component\Chart\ProgressMeter\ProgressMeter::class];
    }
}
