<?php

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\InputData;/**
 * Interface NestedInput
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 * @package ILIAS\UI\Component\Input\Field
 */
interface NestedInput extends FormInput
{
    /**
     * Returns an input like this, with additionally nested sub-inputs.
     *
     * @param Input[] $inputs
     * @return NestedInput
     */
    public function withNestedInputs(array $inputs) : NestedInput;

    /**
     * Returns the nested sub-inputs of this input.
     *
     * @return Input[]|null
     */
    public function getNestedInputs() : ?array;

    /**
     * Get an input like this with input from post data.
     *
     * @param    InputData $post_input
     * @return    NestedInput
     */
    public function withNestedInput(InputData $post_input) : NestedInput;
}