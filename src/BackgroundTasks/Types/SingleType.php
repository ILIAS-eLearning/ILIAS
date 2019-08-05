<?php

namespace ILIAS\BackgroundTasks\Types;

class SingleType implements Type, Ancestors
{

    /** @var \ReflectionClass */
    protected $type;


    /**
     * SingleType constructor.
     *
     * @param $fullyQualifiedClassName
     */
    public function __construct($fullyQualifiedClassName)
    {
        $this->type = new \ReflectionClass($fullyQualifiedClassName);
    }


    /**
     * @inheritdoc
     */
    function __toString()
    {
        return $this->type->getName();
    }


    /**
     * @inheritdoc
     */
    function isExtensionOf(Type $type)
    {
        if (!$type instanceof SingleType) {
            return false;
        }

        return $this->type->isSubclassOf($type->__toString()) || $this->__toString() == $type->__toString();
    }


    /**
     * @inheritdoc
     */
    public function getAncestors()
    {
        $class = $this->type;
        $ancestors = [new SingleType($class->getName())];

        while ($class = $class->getParentClass()) {
            $ancestors[] = new SingleType($class->getName());
        }

        return array_reverse($ancestors);
    }


    /**
     * @inheritdoc
     */
    function equals(Type $otherType)
    {
        if (!$otherType instanceof SingleType) {
            return false;
        }

        return $this->__toString() == $otherType->__toString();
    }
}
