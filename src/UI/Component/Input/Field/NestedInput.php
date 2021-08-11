<?php

namespace ILIAS\UI\Component\Input\Field;

/**
 * Interface NestedInput
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
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
}