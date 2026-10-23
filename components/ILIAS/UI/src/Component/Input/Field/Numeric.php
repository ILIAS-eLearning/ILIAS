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

/**
 * This describes numeric inputs.
 */
interface Numeric extends FilterInput
{
    /**
     * Returns the step size used for this numeric input field.
     * This value specifies the step size used when incrementing or decrementing the field's value
     * via arrow controls, or during validation checks.
     *
     * @return int|float The configured step size of the input field.
     */
    public function getStepSize(): int|float;

    /**
     * This will not only set the steps for the input's arrow controls,
     * but will also alter the field's transformation.
     * The value will be the same type as the parameter given here,
     * so even a $stepsize = 1.0 will result in an float value.
     * This also means that all existing transformations need to be wiped,
     * since the type of the initially contained value changes!
     *
     * ATTENTION: Using withStepSize (and altering the type) will erase
     * all existing transformations on the field.
     * Please re-consider carefully if you really want to use floats at all ;)
     */
    public function withStepSize(int|float $stepsize = 1): self;
}
