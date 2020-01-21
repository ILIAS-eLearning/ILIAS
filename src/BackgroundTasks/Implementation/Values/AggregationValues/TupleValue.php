<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\AggregationValues;

use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

class TupleValue extends AbstractValue
{

    /**
     * @var array
     */
    protected $values = [];
    /**
     * @var string
     */
    protected $type = "";


    /**
     * TupleValue constructor.
     *
     * @param $values
     */
    public function __construct()
    {
    }


    /**
     * String representation of object
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->values);
    }


    /**
     * Constructs the object
     *
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->values = $this->unserialize($serialized);
        $this->type = $this->calculateLowestCommonType($this->values);
    }


    /**
     * @return string Gets a hash for this IO. If two objects are the same the hash must be the
     *                same! if two objects are different you need to have as few collisions as
     *                possible.
     */
    public function getHash()
    {
        $hashes = '';
        foreach ($this->getValues() as $value) {
            $hashes .= $value->getHash();
        }

        return md5($hashes);
    }


    /**
     * @param \ILIAS\BackgroundTasks\Value $other
     *
     * @return mixed
     */
    public function equals(Value $other)
    {
        if (!$other instanceof ListValue) {
            return false;
        }

        if ($this->getType() != $other->getType()) {
            return false;
        }

        $values = $this->getValues();
        $otherValues = $other->getList();

        if (count($values) != count($otherValues)) {
            return false;
        }

        for ($i = 0; $i < count($values); $i++) {
            if (!$values[$i]->equals($otherValues[$i])) {
                ;
            }
        }

        return true;
    }


    /**
     * @return Value[]
     */
    public function getValues()
    {
        return $this->values;
    }


    /**
     * @return string
     * @var string get the Type of the
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param $list Value
     *
     * @return string
     */
    private function constructType($list)
    {
        return "(" + implode(",", $list->getType()) + ")";
    }


    /**
     * @param $values
     *
     */
    public function setValue($values)
    {
        $wrapperFactory = \PrimitiveValueWrapperFactory::getInstance();
        foreach ($values as $value) {
            $this->$values[] = $wrapperFactory->wrapValue($value);
        }

        $this->type = $this->constructType($this->values);
    }
}
