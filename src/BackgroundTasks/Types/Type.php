<?php

namespace ILIAS\BackgroundTasks\Types;

interface Type
{

    /**
     * @return string A string representation of the Type.
     */
    public function __toString();


    /**
     * Is this type a subtype of $type. Not strict! x->isExtensionOf(x) == true.
     *
     * @param Type $type ValueType
     *
     * @return bool
     */
    public function isExtensionOf(Type $type);


    /**
     * returns true if the two types are equal.
     *
     * @param Type $otherType
     *
     * @return bool
     */
    public function equals(Type $otherType);
}