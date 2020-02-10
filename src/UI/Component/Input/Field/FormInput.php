<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Component;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\OnUpdateable;

/**
 * This describes inputs that can be used in forms.
 */
interface FormInput extends Component, Input, JavaScriptBindable, OnUpdateable
{
    /**
     * Get the label of the input.
     *
     * @return    string
     */
    public function getLabel();

    /**
     * Get an input like this, but with a replaced label.
     *
     * @param    string $label
     *
     * @return    Input
     */
    public function withLabel($label);

    /**
     * Get the byline of the input.
     *
     * @return    string|null
     */
    public function getByline();

    /**
     * Get an input like this, but with an additional/replaced label.
     *
     * @param    string|null $byline
     *
     * @return    Input
     */
    public function withByline($byline);

    /**
     * Is this field required?
     *
     * @return    bool
     */
    public function isRequired();

    /**
     * Get an input like this, but set the field to be required (or not).
     *
     * @param    bool $is_required
     *
     * @return    Input
     */
    public function withRequired($is_required);

    /**
     * Is this input disabled?
     *
     * @return    bool
     */
    public function isDisabled();

    /**
     * Get an input like this, but set it to a disabled state.
     *
     * @param    bool $is_disabled
     *
     * @return    Input
     */
    public function withDisabled($is_disabled);

    /**
     * The error of the input as used in HTML.
     *
     * @return string|null
     */
    public function getError();

    /**
     * Get an input like this one, with a different error.
     *
     * @param    string
     *
     * @return    Input
     */
    public function withError($error);

    /**
     * Get update code
     *
     * This method has to return JS code that calls
     * il.UI.filter.onFieldUpdate(event, '$id', string_value);
     * - initially "onload" and
     * - on every input change.
     * It must pass a readable string representation of its value in parameter 'string_value'.
     *
     * @param \Closure $binder
     * @return string
     */
    public function getUpdateOnLoadCode() : \Closure;
}
