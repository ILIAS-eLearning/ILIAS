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

use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Repository\Validation\Dictionary\Restriction;
use ILIAS\MetaData\Repository\Validation\Dictionary\TagInterface as ConstraintTag;

abstract class BaseFactory
{
    protected UIFactory $ui_factory;
    protected PresenterInterface $presenter;
    protected ConstraintDictionary $constraint_dictionary;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary
    ) {
        $this->ui_factory = $ui_factory;
        $this->presenter = $presenter;
        $this->constraint_dictionary = $constraint_dictionary;
    }

    abstract protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        string $condition_value = ''
    ): FormInput;

    /**
     * @return string|string[]
     */
    protected function dataValueForInput(
        DataInterface $data
    ): string|array {
        return $data->value();
    }

    final public function getInput(
        ElementInterface $element,
        ElementInterface $context_element,
    ): FormInput {
        $input = $this->rawInput(
            $element,
            $context_element
        );

        return $this->finishInput($element, $context_element, $input);
    }

    final public function getInputInCondition(
        ElementInterface $element,
        ElementInterface $context_element,
        string $condition_value
    ): FormInput {
        $input = $this->rawInput(
            $element,
            $context_element,
            $condition_value
        );

        return $this->finishInputIgnoreValue($element, $context_element, $input);
    }

    public function finishInput(
        ElementInterface $element,
        ElementInterface $context_element,
        FormInput $input
    ): FormInput {
        if (($data = $element->getData())->type() !== Type::NULL) {
            $input = $input->withValue(
                $this->dataValueForInput($data)
            );
        }

        return $this->finishInputIgnoreValue($element, $context_element, $input);
    }

    protected function finishInputIgnoreValue(
        ElementInterface $element,
        ElementInterface $context_element,
        FormInput $input
    ): FormInput {
        $label = $this->presenter->elements()->nameWithParents(
            $element,
            $context_element,
            false
        );
        $input = $input->withLabel($label);

        foreach ($this->constraint_dictionary->tagsForElement($element) as $tag) {
            $input = $this->addConstraintFromTag($input, $tag);
        }

        return $input;
    }

    protected function addConstraintFromTag(
        FormInput $input,
        ConstraintTag $tag
    ): FormInput {
        switch ($tag->restriction()) {
            case Restriction::PRESET_VALUE:
                return $input->withValue($tag->value());

            case Restriction::NOT_DELETABLE:
                return $input->withRequired(true);

            case Restriction::NOT_EDITABLE:
                return $input->withDisabled(true);
        }
        return $input;
    }
}
