<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

class ScalarValue extends AbstractValue
{

    /**
     * @var mixed is_scalar() == true;
     */
    protected $value;


    /**
     * ScalarValue constructor. Given value must resolve to true when given to is_scalar.
     */
    function __construct()
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
        return serialize($this->value);
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
        $this->value = unserialize($serialized);
    }


    /**
     * @return string Gets a hash for this IO. If two objects are the same the hash must be the
     *                same! if two objects are different you need to have as view collitions as
     *                possible.
     */
    public function getHash()
    {
        return md5($this->serialize());
    }


    /**
     * @param \ILIAS\BackgroundTasks\Value $other
     *
     * @return mixed
     */
    public function equals(Value $other)
    {
        if (!$other instanceof ScalarValue) {
            return false;
        }

        return $this->value == $other->getValue();
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param mixed $value
     *
     * @return void
     * @throws InvalidArgumentException
     */
    function setValue($value)
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException("The value given must be a scalar! See php-documentation is_scalar().");
        }

        $this->value = $value;
    }
}
