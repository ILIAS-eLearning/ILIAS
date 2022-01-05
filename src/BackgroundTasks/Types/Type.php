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
     */
    public function isExtensionOf(Type $type) : bool;
    
    /**
     * returns true if the two types are equal.
     */
    public function equals(Type $otherType) : bool;
}
