<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface DynamicInputsAware extends FormInput
{
    /**
     * Provides an instance of Input, which is used to generate dynamic
     * inputs on clientside.
     */
    public function withTemplateForDynamicInputs(Input $template) : self;

    /**
     * Returns the instance of Input which should be used to generate
     * dynamic inputs on clientside.
     */
    public function getTemplateForDynamicInputs() : ?Input;

    /**
     * Returns serverside generated dynamic Inputs, which happens when
     * providing this withValue()
     * @return Input[]
     */
    public function getDynamicInputs() : array;
}