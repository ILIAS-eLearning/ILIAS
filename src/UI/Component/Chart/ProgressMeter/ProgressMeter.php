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
 
namespace ILIAS\UI\Component\Chart\ProgressMeter;

use ILIAS\UI\Component\Component;

/**
 * Interface ProgressMeter
 * @package ILIAS\UI\Component\Chart\ProgressMeter
 */
interface ProgressMeter extends Component
{
    /**
     * Get maximum value
     *
     * This value is used as 100%.
     * This value will always return "raw" because it is used to calculate the
     * percentage values of main, required and comparison.
     *
     * @return int|float
     */
    public function getMaximum();

    /**
     * Get required value
     *
     * This value represents the required amount that is needed, to fulfill the objective.
     * If this value is not set, it defaults to the maximum.
     *
     * @return int|float|null
     */
    public function getRequired();

    /**
     * Get main value
     *
     * This value is represented as the main progress meter bar.
     *
     * @return int|float
     */
    public function getMainValue();
}
