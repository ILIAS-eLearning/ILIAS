<?php

namespace ILIAS\BackgroundTasks\Types;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
