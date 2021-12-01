<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * DynamicInputsNameSource is responsible for generating
 * names for dynamic sub-inputs.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsNameSource implements NameSource
{
    /**
     * @var string placeholder used for indexing the subordinate
     *             input names on clientside.
     */
    public const INDEX_PLACEHOLDER = 'DYNAMIC_INPUT_INDEX';

    private int $count = 0;
    private string $parent_input_name;

    /**
     * @param string $parent_input_name will be used for the post
     *                                  array name.
     */
    public function __construct(string $parent_input_name)
    {
        $this->parent_input_name = $parent_input_name;
    }

    /**
     * Returns a name that is "mapped" to the parent-input-name
     * this source got instantiated with.
     *
     * NOTE that the index placeholder within the first two
     * brackets must be replaced on client-side, in order to
     * retrieve valid $_POST values.
     */
    public function getNewName() : string
    {
        return "$this->parent_input_name[" . self::INDEX_PLACEHOLDER . '][dynamic_input_' . $this->count++ . ']';
    }
}