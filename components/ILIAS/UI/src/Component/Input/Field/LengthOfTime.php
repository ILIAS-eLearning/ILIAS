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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use DateInterval;

/**
 * This describes the duration input.
 */
interface LengthOfTime extends Group, FilterInput
{
    /**
     *
     * @param LengthOfTimeFieldPatterns $fieldPattern
     * @return self
     */
    public function withFieldPattern(LengthOfTimeFieldPatterns $fieldPattern): self;

    /**
     * Minimum duration length
     */
    public function withMinValue(DateInterval $date): Duration;


    public function getMinValue(): ?DateInterval;

    /**
     * Maximum Duration Length
     */
    public function withMaxValue(DateInterval $date): Duration;

    /**
     * Return the maximum date the input accepts.
     */
    public function getMaxValue(): ?DateInterval;
}
