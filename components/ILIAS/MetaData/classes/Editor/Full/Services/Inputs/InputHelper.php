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
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Repository\Validation\Dictionary\Restriction;
use ILIAS\MetaData\Repository\Validation\Dictionary\TagInterface as ConstraintTag;

trait InputHelper
{
    protected function getInputLabelFromElement(
        PresenterInterface $presenter,
        ElementInterface $element,
        ElementInterface $context_element
    ): string {
        return $presenter->elements()->nameWithParents(
            $element,
            $context_element,
            false
        );
    }

    protected function addValueFromElement(
        ElementInterface $element,
        FormInput $input
    ): FormInput {
        if ($element->getData()->type() === Type::NULL) {
            return $input;
        }
        return $input->withValue($element->getData()->value());
    }

    protected function addConstraintsFromElement(
        ConstraintDictionary $constraint_dictionary,
        ElementInterface $element,
        FormInput $input,
        bool $skip_value = false
    ): FormInput {
        foreach ($constraint_dictionary->tagsForElement($element) as $tag) {
            $input = $this->addConstraintFromTag($input, $tag, $skip_value);
        }
        return $input;
    }

    private function addConstraintFromTag(
        FormInput $input,
        ConstraintTag $tag,
        bool $skip_value
    ): FormInput {
        switch ($tag->restriction()) {
            case Restriction::PRESET_VALUE:
                if ($skip_value) {
                    return $input;
                }
                return $input->withValue($tag->value());

            case Restriction::NOT_DELETABLE:
                return $input->withRequired(true);

            case Restriction::NOT_EDITABLE:
                return $input->withDisabled(true);
        }
        return $input;
    }

    protected function getPresetValueFromConstraints(
        ConstraintDictionary $constraint_dictionary,
        ElementInterface $element
    ): ?string {
        $preset_value = null;
        foreach ($constraint_dictionary->tagsForElement($element) as $tag) {
            if ($tag->restriction() !== Restriction::PRESET_VALUE) {
                continue;
            }
            $preset_value = $tag->value();
        }
        return $preset_value;
    }
}
