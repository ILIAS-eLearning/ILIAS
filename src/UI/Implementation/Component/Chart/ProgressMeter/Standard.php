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
