<?php

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Class SubordinateNameSource
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class SubordinateNameSource implements NameSource
{
    /**
     * @var int
     */
    private int $count = 0;

    /**
     * @var string
     */
    private string $parent_input_name;

    /**
     * SubordinateNameSource Constructor
     *
     * @param string $parent_input_name
     */
    public function __construct(string $parent_input_name)
    {
        $this->parent_input_name = $parent_input_name;
    }

    /**
     * @inheritDoc
     */
    public function getNewName() : string
    {
        $this->count++;

        return "$this->parent_input_name[][input_$this->count]";
    }
}