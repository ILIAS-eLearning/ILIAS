<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation\Transformations;

use ILIAS\Transformation\Transformation;

/**
 * Adds to any array keys for each value
 */
class AddLabels implements Transformation
{
    /**
     * @var string[] | int[]
     */
    protected $labels;

    /**
     * @param string[] | int[]	$labels
     */
    public function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!is_array($from)) {
            throw new \InvalidArgumentException(__METHOD__ . " argument is not an array.");
        }

        if (count($from) != count($this->labels)) {
            throw new \InvalidArgumentException(__METHOD__ . " number of items in arrays are not equal.");
        }

        return array_combine($this->labels, $from);
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
