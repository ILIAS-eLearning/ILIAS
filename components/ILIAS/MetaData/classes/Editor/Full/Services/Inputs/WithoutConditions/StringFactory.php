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

namespace ILIAS\MetaData\Editor\Full\Services\Inputs\WithoutConditions;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;

class StringFactory extends BaseFactory
{
    protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        string $condition_value = ''
    ): FormInput {
        $super_name = $element->getSuperElement()
                              ->getDefinition()
                              ->name();
        if ($super_name === 'description') {
            return $this->ui_factory->textarea('placeholder');
        }
        return $this->ui_factory->text('placeholder');
    }
}
