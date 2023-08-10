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

use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface HasDynamicInputs extends FormInput
{
    /**
     * Returns the instance of Input which should be used to generate
     * dynamic inputs on clientside.
     */
    public function getTemplateForDynamicInputs(): FormInput;

    /**
     * Returns serverside generated dynamic Inputs, which happens when
     * providing this withValue()
     * @return FormInput[]
     */
    public function getDynamicInputs(): array;
}
