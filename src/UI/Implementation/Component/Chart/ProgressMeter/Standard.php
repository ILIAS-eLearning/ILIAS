<?php declare(strict_types=1);

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\ProgressMeter;

use ILIAS\UI\Component as C;

/**
 * Class ProgressMeter
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class Standard extends ProgressMeter implements C\Chart\ProgressMeter\Standard
{
    protected ?string $main_text = null;
    protected ?string $required_text = null;

    /**
     * @inheritdoc
     */
    public function getComparison()
    {
        return $this->getSafe($this->comparison);
    }

    /**
     * Get comparison value as percent
     */
    public function getComparisonAsPercent() : int
    {
        return $this->getAsPercentage($this->comparison);
    }

    /**
     * @inheritdoc
     */
    public function withMainText(string $text) : C\Chart\ProgressMeter\ProgressMeter
    {
        $this->checkStringArg("main_value_text", $text);

        $clone = clone $this;
        $clone->main_text = $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getMainText() : ?string
    {
        return $this->main_text;
    }

    /**
     * @inheritdoc
     */
    public function withRequiredText(string $text) : C\Chart\ProgressMeter\ProgressMeter
    {
        $this->checkStringArg("required_value_text", $text);

        $clone = clone $this;
        $clone->required_text = $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getRequiredText() : ?string
    {
        return $this->required_text;
    }
}
