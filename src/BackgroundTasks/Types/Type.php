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

namespace ILIAS\BackgroundTasks\Types;

interface Type
{
    /**
     * @return string A string representation of the Type.
     */
    public function __toString(): string;

    /**
     * Is this type a subtype of $type. Not strict! x->isExtensionOf(x) == true.
     */
    public function isExtensionOf(Type $type): bool;

    /**
     * returns true if the two types are equal.
     */
    public function equals(Type $otherType): bool;
}
