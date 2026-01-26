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

namespace ILIAS\Component\Activities;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Input;

/**
 * An Activity that refers to a certain object in the system. This also is a
 * generic interface but a subset of inputs is fixed.
 *
 * This does not necessarily need to refer to an ilObject, but can also be used
 * to refer to any objects internal to a given component, such as questions,
 * memberships or certain results.
 */
interface ObjectActivity extends Activity
{
    /**
     * @inheritdoc
     *
     * For an ObjectActivity, this needs to return an input with one field named
     * "id" that can accept a string value.
     */
    public function getInputDescription(): FormInput;

    /**
     * Works just like `getInputDescription` but checks if that description
     * matches the spec.
     *
     * @throws \LogicException if there is no "id" field in the Input.
     */
    public function getCheckedInputDescription(): Input;

    /**
     * To allow consumers to build an understanding of internal object structure of
     * a component, an ObjectActivity needs to tell a "type" of object it touches.
     *
     * The type should be a short alphanumeric string.
     */
    public function getTargetType(): string;
}
