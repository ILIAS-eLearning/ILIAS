<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\BackgroundTasks\Implementation\Values\AggregationValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BasicScalarValueFactory;
use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Class ListValue
 * @package ILIAS\BackgroundTasks\Implementation\Values
 * The type of the list will be the lowest common type in the list e.g. [ScalarValue] if its a list
 * containing IntegerValues and FloatValues.
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class ListValue extends AbstractValue
{
    use BasicScalarValueFactory;

    /**
     * @var array The values of the list are saved in an array.
     */
    protected array $list = [];
    protected Type $type;

    protected function deriveType(array $list): ListType
    {
        $type = $this->calculateLowestCommonType($this->getTypes($list));

        return new ListType($type);
    }

    /**
     * Todo: This implementation is not performing well (needs the most iterations) on lists with
     * all the same type, this might be suboptimal.
     * @param $types Type[]
     * @return null|mixed
     * @throws InvalidArgumentException
     */
    protected function calculateLowestCommonType(array $types)
    {
        // If the list is empty the type should be [] (empty list).
        if ($types === []) {
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

    protected function sameClassOnLevel(array $ancestorsList, int $i): bool
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

    /**
     * @return \ILIAS\BackgroundTasks\Types\Type[]
     */
    protected function getTypes(array $list): array
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
     * @link  http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
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
    public function getHash(): string
    {
        $hashes = '';
        foreach ($this->getList() as $value) {
            $hashes .= $value->getHash();
        }

        return md5($hashes);
    }

    public function equals(Value $other): bool
    {
        if (!$other instanceof ListValue) {
            return false;
        }

        if ($this->getType() != $other->getType()) {
            return false;
        }

        $values = $this->getList();
        $otherValues = $other->getList();

        if (count($values) !== count($otherValues)) {
            return false;
        }

        foreach ($values as $i => $value) {
            if (!$value->equals($otherValues[$i])) {
                ;
            }
        }

        return true;
    }

    /**
     * @return Value[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @param $object
     * @return class-string[]
     * @throws InvalidArgumentException
     */
    protected function getClassHierarchy($object): array
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

    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @param $value
     */
    public function setValue($value): void
    {
        $this->type = $this->deriveType($value);
    }
}
