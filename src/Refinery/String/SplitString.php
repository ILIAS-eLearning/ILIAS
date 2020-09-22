<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;

/**
 * Split a string by delimiter into array
 */
class SplitString implements Transformation
{
    use DeriveInvokeFromTransform;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param string $delimiter
     * @param Factory $factory
     */
    public function __construct($delimiter, Factory $factory)
    {
        $this->delimiter = $delimiter;
        $this->factory = $factory;
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
    public function applyTo(Result $data) : Result
    {
        $dataValue = $data->value();
        if (false === is_string($dataValue)) {
            $exception = new \InvalidArgumentException(__METHOD__ . " the argument is not a string.");
            return $this->factory->error($exception);
        }

        $value = explode($this->delimiter, $dataValue);
        return $this->factory->ok($value);
    }
}
