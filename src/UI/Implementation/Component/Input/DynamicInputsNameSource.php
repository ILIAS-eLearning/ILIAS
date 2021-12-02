<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsNameSource extends DynamicInputsTemplateNameSource
{
    protected int $absolute_input_count;

    public function __construct(string $parent_input_name, int $absolute_input_count)
    {
        parent::__construct($parent_input_name);
        $this->absolute_input_count = $absolute_input_count;
    }

    public function getNewName() : string
    {
        return "$this->parent_input_name[$this->absolute_input_count][dynamic_input_" . $this->input_count++ . ']';
    }
}