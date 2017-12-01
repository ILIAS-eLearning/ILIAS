<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\Speedo;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Chart\Speedo
 */
class Renderer extends AbstractComponentRenderer
{

    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        /**
         * @var Component\Chart\Speedo\Speedo $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Chart\Speedo\Responsive) {
            /**
             * @var Component\Chart\Speedo\Responsive $component
             */
            return $this->renderResponsive($component, $default_renderer);

        } elseif ($component instanceof Component\Chart\Speedo\Mini) {
            /**
             * @var Component\Chart\Speedo\Mini $component
             */
            return $this->renderMini($component, $default_renderer);

        } else {
            /**
             * @var Component\Chart\Speedo\Standard $component
             */
            return $this->renderStandard($component, $default_renderer);
        }

    }

    /**
     * Render standard speedo
     *
     * @param Component\Component $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderStandard(Component\Chart\Speedo\Standard $component, RendererInterface $default_renderer)
    {
        if ($component->hasDiagnostic()) {
            $tpl = $this->getTemplate("tpl.speedo_two_bar.html", true, true);
        } else {
            $tpl = $this->getTemplate("tpl.speedo_one_bar.html", true, true);
        }

        // set "responsive class" false
        $tpl->setVariable("RESP_CLASS", 'standard');

        // set visible values
        $tpl = $this->modifyVisibleValues($tpl, $component);

        // set skew and rotation for process bars
        $tpl = $this->modifyProgressBar($tpl, $component->getScore(), 'SCORE');
        if ($component->hasDiagnostic()) {
            $tpl = $this->modifyProgressBar($tpl, $component->getDiagnostic(), 'TEST');
        }

        // set progress bar color class
        $tpl = $this->modifyProgressBarClasses($tpl, $component);

        // set marker position
        if($component->getMinimum(false) != $component->getGoal()) {
            $tpl->setVariable("MARKER_POS", $this->getMarkerPos($component->getMinimum()));
        } else {
            $tpl->setVariable("MARKER_POS",'180');
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Render responsive speedo
     *
     * @param Component\Component $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderResponsive(Component\Chart\Speedo\Standard $component, RendererInterface $default_renderer)
    {
        if ($component->hasDiagnostic()) {
            $tpl = $this->getTemplate("tpl.speedo_two_bar.html", true, true);
        } else {
            $tpl = $this->getTemplate("tpl.speedo_one_bar.html", true, true);
        }

        // set "responsive class" false
        $tpl->setVariable("RESP_CLASS", 'responsive');

        // set visible values
        $tpl = $this->modifyVisibleValues($tpl, $component);

        // set skew and rotation for process bars
        $tpl = $this->modifyProgressBar($tpl, $component->getScore(), 'SCORE');
        if ($component->hasDiagnostic()) {
            $tpl = $this->modifyProgressBar($tpl, $component->getDiagnostic(), 'TEST');
        }

        // set progress bar color class
        $tpl = $this->modifyProgressBarClasses($tpl, $component);

        // set marker position
        if($component->getMinimum(false) != $component->getGoal()) {
            $tpl->setVariable("MARKER_POS", $this->getMarkerPos($component->getMinimum()));
        } else {
            $tpl->setVariable("MARKER_POS",'180');
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Render mini speedo
     *
     * @param Component\Component $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function renderMini(Component\Chart\Speedo\Mini $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.speedo_mini.html", true, true);

        // set skew and rotation for process bars
        $tpl = $this->modifyProgressBar($tpl, $component->getScore(), 'SCORE');

        // set progress bar color class
        $tpl = $this->modifyProgressBarClasses($tpl, $component);

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * Modify visible template variables
     *
     * @param \ILIAS\UI\Implementation\Render\Template $tpl
     * @param Component\Chart\Speedo\Standard $component
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    protected function modifyVisibleValues(\ILIAS\UI\Implementation\Render\Template $tpl, Component\Component $component)
    {
        $tpl->setVariable("SCORE", $component->getScore().' %');
        if($component->getMinimum(false) != $component->getGoal()) {
            $tpl->setVariable("GOAL_SCORE", $component->getMinimum().' %');
        } else {
            $tpl->setVariable("GOAL_SCORE", '');
        }
        if ($component->hasDiagnostic()) {
            $tpl->setVariable("TEST_SCORE", $component->getDiagnostic().' %');
        }
        $tpl->setVariable("TXT_SCORE", htmlspecialchars($component->getTxtScore()));
        $tpl->setVariable("TXT_GOAL_SCORE", htmlspecialchars($component->getTxtGoal()));
        return $tpl;
    }

    /**
     * Modify the template skew and rotation variables for a specific progress bar, identified by its prefix
     *
     * @param \ILIAS\UI\Implementation\Render\Template $tpl
     * @param float $score
     * @param string $prefix
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    protected function modifyProgressBar(\ILIAS\UI\Implementation\Render\Template $tpl, $score, $prefix)
    {
        $skew_score = $this->getSkew($score);
        $tpl->setVariable("SKEW_".$prefix, $skew_score);
        $rotation_score = $this->getRotation($skew_score);
        $tpl->setVariable("ROTATION_".$prefix."1", $rotation_score[0]);
        $tpl->setVariable("ROTATION_".$prefix."2", $rotation_score[1]);
        $tpl->setVariable("ROTATION_".$prefix."3", $rotation_score[2]);
        return $tpl;
    }

    /**
     * Modify the template variables for the progress bar classes, used for colors
     *
     * @param \ILIAS\UI\Implementation\Render\Template $tpl
     * @param Component\Chart\Speedo\Standard $component
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    protected function modifyProgressBarClasses(\ILIAS\UI\Implementation\Render\Template $tpl, Component\Component $component)
    {
        $tpl->setVariable("SCORE_BAR_CLASS", '');
        $tpl->setVariable("TEST_BAR_CLASS", '');
        if ($this->getIsScoreSet($component->getScore())) {
            $tpl->setVariable(
                "SCORE_BAR_CLASS",
                (
                $this->getIsGoalReached($component->getScore(), $component->getMinimum()) ?
                    'il-chart-speedo-green' :
                    'il-chart-speedo-red'
                )
            );
        } else {
            if ($component->hasDiagnostic()) {
                $tpl->setVariable("TEST_BAR_CLASS", 'il-chart-speedo-yellow');
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
        $rotation[1] = (-25 + ((90 - $skew) * 1));
        $rotation[2] = (-25 + ((90 - $skew) * 2));
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
     * Test if score is not zero
     *
     * @param int|float $score
     * @return bool
     */
    protected function getIsScoreSet($score)
    {
        return ($score > 0);
    }

    /**
     * Test if score has reached goal
     *
     * @param int|float $score
     * @param int|float $goal
     * @return bool
     */
    protected function getIsGoalReached($score, $goal)
    {
        return ($score >= $goal);
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [Component\Chart\Speedo\Speedo::class];
    }
}