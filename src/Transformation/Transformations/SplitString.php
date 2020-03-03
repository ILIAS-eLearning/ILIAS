<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation\Transformations;

use ILIAS\Transformation\Transformation;

/**
 * Split a string by delimiter into array
 */
class SplitString implements Transformation
{
    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @param string 	$delimiter
     */
    public function __construct($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!is_string($from)) {
            throw new \InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        return explode($this->delimiter, $from);
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
