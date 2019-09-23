<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\AggregationValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Implementation\Values\PrimitiveValueWrapperFactory;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BasicScalarValueFactory;
use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\ValueType;

/**
 * Class ListValue
 *
 * @package ILIAS\BackgroundTasks\Implementation\Values
 *
 * The type of the list will be the lowest common type in the list e.g. [ScalarValue] if its a list
 * containing IntegerValues and FloatValues.
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class ListValue extends AbstractValue
{

    use BasicScalarValueFactory;
    /**
     * @var array The values of the list are saved in an array.
     */
    protected $list = array();
    /**
     * @var Type
     */
    protected $type;


    /**
     * ListValue constructor.
     *
     * @param $list array
     */
    public function __construct()
    {
    }


    protected function deriveType($list)
    {
        return new ListType($this->calculateLowestCommonType($this->getTypes($list)));
    }


    /**
     * Todo: This implementation is not performing well (needs the most iterations) on lists with
     * all the same type, this might be suboptimal.
     *
     * @param $types ListType[]
     *
     * @return Type
     * @throws InvalidArgumentException
     */
    protected function calculateLowestCommonType($types)
    {
        // If the list is empty the type should be [] (empty list).
        if (!count($types)) {
            return null;
        }

        if (count($types) == 1) {
            return $types[0];
        }

        $ancestorsList = [];
        foreach ($types as $object) {
            if (!$object instanceof Type) {
                throw new InvalidArgumentException("List Type must be constructed with instances of Type.");
            }
            $ancestorsList[] = $object->getAncestors();
        }

        $lct = $ancestorsList[0][0];
        foreach ($ancestorsList[0] as $i => $ancestors) {
            if ($this->sameClassOnLevel($ancestorsList, $i)) {
                $lct = $ancestors;
            } else {
                return $lct;
            }
        }

        // We reach this point if the types are equal.
        return $lct;
    }


    /**
     * @param $ancestorsList Type[][]
     * @param $i
     *
     * @return bool
     */
    protected function sameClassOnLevel($ancestorsList, $i)
    {
        $class = $ancestorsList[0][$i];
        foreach ($ancestorsList as $class_hierarchy) {
            if (count($class_hierarchy) <= $i) {
                return false;
            }
            if (!$class_hierarchy[$i]->equals($class)) {
                return false;
            }
        }

        return true;
    }


    protected function getTypes($list)
    {
        $types = [];
        foreach ($list as $value) {
            $valueWrapped = $this->wrapValue($value);
            $this->list[] = $valueWrapped;
            $types[] = $valueWrapped->getType();
        }

        return $types;
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
        return serialize($this->list);
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
        $this->list = unserialize($serialized);
        $this->type = $this->deriveType($this->list);
    }


    /**
     * @return string Gets a hash for this IO. If two objects are the same the hash must be the
     *                same! if two objects are different you need to have as few collisions as
     *                possible.
     */
    public function getHash()
    {
        $hashes = '';
        foreach ($this->getList() as $value) {
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

        $values = $this->getList();
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
    public function getList()
    {
        return $this->list;
    }


    /**
     * @param $object
     *
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getClassHierarchy($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Given Value $object must be an object.");
        }

        $hierarchy = [];
        $class = get_class($object);

        do {
            $hierarchy[] = $class;
        } while (($class = get_parent_class($class)) !== false);

        return $hierarchy;
    }


    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param $list
     *
     */
    function setValue($list)
    {
        $this->type = $this->deriveType($list);
    }
}