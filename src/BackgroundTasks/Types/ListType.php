<?php

namespace ILIAS\BackgroundTasks\Types;

/**
 * Class ListType
 *
 * @package ILIAS\Types
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * Describes a list of a certain Type.
 *
 * Attention:
 * isExtensionOf behaves Covariant!
 * new ListType(Dog::class).isExtensionOf(new ListType(Animal::class)) == true!
 *
 * See: https://en.wikipedia.org/wiki/Covariance_and_contravariance_(computer_science)
 */
class ListType implements Type, Ancestors
{

    /** @var Type */
    protected $type;


    /**
     * SingleType constructor.
     *
     * @param $fullyQualifiedClassName string|Type Give a Value Type or a Type that will be wrapped
     *                                 in a single type.
     */
    public function __construct($fullyQualifiedClassName)
    {
        if (!is_a($fullyQualifiedClassName, Type::class)) {
            $fullyQualifiedClassName = new SingleType($fullyQualifiedClassName);
        }
        $this->type = $fullyQualifiedClassName;
    }


    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return "[" . $this->type . "]";
    }


    /**
     * Is this type a subtype of $type. Not strict! x->isExtensionOf(x) == true.
     *
     * Attention: [Dog].isExtensionOf([Animal]) == true. In other words:
     * The isExtensionOf behaves Covariant!
     *
     * If you are familiar with e.g. Java Generics ?.isExtensionOf(x) behaves the same as <? extends x>.
     *
     * See: http://stackoverflow.com/questions/2575363/generics-list-extends-animal-is-same-as-listanimal
     *
     * See: https://en.wikipedia.org/wiki/Covariance_and_contravariance_(computer_science)
     *
     * @param $type Type
     *
     * @return bool
     */
    public function isExtensionOf(Type $type)
    {
        if (!$type instanceof ListType) {
            return false;
        }

        return $this->type->isExtensionOf($type->getContainedType());
    }


    /**
     * @return Type
     */
    public function getContainedType()
    {
        return $this->type;
    }


    /**
     * @inheritdoc
     */
    public function getAncestors()
    {
        $ancestors = [];

        foreach ($this->type->getAncestors() as $type) {
            $ancestors[] = new ListType($type);
        }

        return $ancestors;
    }


    /**
     * @inheritdoc
     */
    public function equals(Type $otherType)
    {
        if (!$otherType instanceof ListType) {
            return false;
        }

        return $this->type->equals($otherType->getContainedType());
    }
}
