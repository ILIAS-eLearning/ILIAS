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
 */

namespace ILIAS\UI\Component\Input;

/**
 * Groups are a special kind of input because they are a monoid operation.
 *
 * This means, grouping together inputs must always result in another input of
 * the same type. However, this is not true if we mix up different kinds of
 * inputs, such as form inputs and view-control inputs. Therefore, we need to
 * distinguish between different kinds of groups for all container-specific
 * inputs.
 *
 * This interface describes a monoid operation on the most general level, which
 * is not something we need at the moment. Instead, we use this interface to
 * recognize these operations and to describe the commonalities between the
 * different input groups.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Group extends Input
{
    /**
     * @return Input[]
     */
    public function getInputs(): array;
}
