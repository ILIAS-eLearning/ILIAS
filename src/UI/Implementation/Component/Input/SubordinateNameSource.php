<?php

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Class SubordinateNameSource is responsible for generating
 * names for additional sub-inputs.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class SubordinateNameSource implements NameSource
{
    /**
     * @var string placeholder used for indexing the subordinate
     *             input names on clientside.
     */
    public const INDEX_PLACEHOLDER = '{INDEX}';

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
     * Returns a name that is "mapped" to the parent-input-name
     * this source got instantiated with.
     *
     * NOTE that the '{INDEX}' placeholder within the first two
     * brackets must be replaced on client-side, in order to
     * retrieve valid $_POST values.
     *
     * @inheritDoc
     */
    public function getNewName() : string
    {
        $this->count++;

        return "$this->parent_input_name[" . self::INDEX_PLACEHOLDER . "][input_$this->count]";
    }
}