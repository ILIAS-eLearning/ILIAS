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

declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\ViewControl\ViewControlInput;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * Group inputs are a special kind of input, because they are a monoid operation.
 * This means, grouping together inputs of the same type must result in an input
 * of the same type as well, which is why this interface also needs to implement
 * every container-specific input interface that can use this mechanism. Please
 * note that grouping e.g. ViewControlInput with FormInput will lead to sameness
 * in form of their first common interface, which is Input. Grouping together
 * inputs of the same kind is therefore recommended, to avoid unexpected results.
 *
 * FilterInput is not yet implemented because Filters do not yet support groups.
 * This will most likely change in the future.
 */
interface Group extends ViewControlInput, FormInput
{
}
