<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation\Transformations;

use ILIAS\Transformation\Transformation;
use ILIAS\Data\Factory as DataFactory;

/**
 * Convert a primitive to a data type.
 */
class Data implements Transformation
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string 	$type
     */
    public function __construct($type)
    {
        $this->type = $type;
        if (!method_exists($this->getDataFactory(), $type)) {
            throw new \InvalidArgumentException("No such type to transform to: $type");
        }
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        $type = $this->type;
        $data_factory = $this->getDataFactory();
        return $data_factory->$type($from);
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }

    /**
     * Get an instance of the data-factory
     * @return Data\Factory
     */
    protected function getDataFactory()
    {
        return new DataFactory();
    }
}
