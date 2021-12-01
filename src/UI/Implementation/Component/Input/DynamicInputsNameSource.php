<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Other than the FormInputNameSource this name source is for inputs
 * that can be dynamically added multiple times on clientside,
 * therefore it must provide a name that is stacked when submitted to
 * the backend.
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsNameSource extends FormInputNameSource
{
    protected string $parent_input_name;

    public function __construct(string $parent_input_name)
    {
        $this->parent_input_name = $parent_input_name;
    }

    public function getNewName() : string
    {
        return "$this->parent_input_name[" . parent::getNewName() . "][]";
    }
}