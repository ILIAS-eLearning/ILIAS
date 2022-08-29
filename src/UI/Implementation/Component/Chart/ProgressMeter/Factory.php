<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\Chart\ProgressMeter;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class Factory implements ProgressMeter\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($maximum, $main, $required = null, $comparison = null): ProgressMeter\Standard
    {
        return new Standard($maximum, $main, $required, $comparison);
    }
    /**
     * @inheritdoc
     */
    public function fixedSize($maximum, $main, $required = null, $comparison = null): ProgressMeter\FixedSize
    {
        return new FixedSize($maximum, $main, $required, $comparison);
    }

    /**
     * @inheritdoc
     */
    public function mini($maximum, $main, $required = null): ProgressMeter\Mini
    {
        return new Mini($maximum, $main, $required);
    }
}
