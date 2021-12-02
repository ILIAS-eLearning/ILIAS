<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsTemplateNameSource implements NameSource
{
    public const INDEX_PLACEHOLDER = 'DYNAMIC_INPUT_INDEX';

    protected string $parent_input_name;
    protected int $input_count = 0;

    public function __construct(string $parent_input_name)
    {
        $this->parent_input_name = $parent_input_name;
    }

    public function getNewName() : string
    {
        return "$this->parent_input_name[" . self::INDEX_PLACEHOLDER . "][dynamic_input_" . $this->input_count++ . ']';
    }
}