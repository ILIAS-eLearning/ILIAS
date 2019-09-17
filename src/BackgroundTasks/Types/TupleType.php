<?php

namespace ILIAS\BackgroundTasks\Types;

class TupleType implements Type
{

    /**
     * @var Type[]
     */
    protected $types = [];


    /**
     * SingleType constructor.
     *
     * @param $fullyQualifiedClassNames (string|Type)[] Give a Value Type or a Type that will be wrapped in a single type.
     */
    public function __construct($fullyQualifiedClassNames)
    {
        foreach ($fullyQualifiedClassNames as $fullyQualifiedClassName) {
            if (!is_a($fullyQualifiedClassName, Type::class)) {
                $fullyQualifiedClassName = new SingleType($fullyQualifiedClassName);
            }
            $this->types[] = $fullyQualifiedClassName;
        }
    }


    /**
     * @inheritdoc
     */
    function __toString()
    {
        return "(" . implode(", ", $this->types) . ")";
    }


    /**
     * @param Type $type
     *
     * tuple A is a subtype of tuple B, iff every element i of tuple A is a subtype of element i of
     * tuple B.
     *
     * @return bool
     */
    function isExtensionOf(Type $type)
    {
        if (!$type instanceof TupleType) {
            return false;
        }

        $others = $type->getTypes();
        for ($i = 0; $i < count($this->types); $i++) {
            if (!$this->types[$i]->isExtensionOf($others[$i])) {
                return false;
            }
        }

        return true;
    }


    public function getTypes()
    {
        return $this->types;
    }


    /**
     * @inheritdoc
     */
    function equals(Type $otherTuple)
    {
        if (!$otherTuple instanceof TupleType) {
            return false;
        }

        foreach ($this->types as $i => $type) {
            $otherTypes = $otherTuple->getTypes();
            if (!$otherTypes[$i]->equals($type)) {
                return false;
            }
        }

        return true;
    }
}