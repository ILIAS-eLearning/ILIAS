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

namespace ILIAS\UI\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\OnUpdateable;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * This describes inputs that can be used in forms.
 */
interface FormInput extends Input, JavaScriptBindable, OnUpdateable
{
    /**
     * Get the label of the input.
     */
    public function getLabel(): string;

    /**
     * Get an input like this, but with a replaced label.
     *
     * @return static
     */
    public function withLabel(string $label);

    /**
     * Get the byline of the input.
     */
    public function getByline(): ?string;

    /**
     * Get an input like this, but with an additional/replaced label.
     *
     * @return static
     */
    public function withByline(string $byline);

    /**
     * Is this field required?
     */
    public function isRequired(): bool;

    /**
     * Get an input like this, but set the field to be required (or not).
     * With the optional $required_constraint, you can REPLACE the default
     * constraint that is checked if $is_required is true
     * (see getConstraintForRequirement() on Input/Field implementations).
     * A custom constraint SHOULD be explained in the byline of the input.
     */
    public function withRequired(bool $is_required, ?Constraint $requirement_constraint = null): self;

    /**
     * Is this input disabled?
     */
    public function isDisabled(): bool;

    /**
     * Get an input like this, but set it to a disabled state.
     *
     * @return static
     */
    public function withDisabled(bool $is_disabled);

    /**
     * Get update code
     *
     * This method has to return JS code that calls
     * il.UI.filter.onFieldUpdate(event, '$id', string_value);
     * - initially "onload" and
     * - on every input change.
     * It must pass a readable string representation of its value in parameter 'string_value'.
     */
    public function getUpdateOnLoadCode(): Closure;
}
