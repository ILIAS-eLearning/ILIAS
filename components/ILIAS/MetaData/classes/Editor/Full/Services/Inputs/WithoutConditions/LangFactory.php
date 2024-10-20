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
use ILIAS\MetaData\DataHelper\DataHelperInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Elements\Data\Type;

class LangFactory extends BaseFactory
{
    protected DataHelperInterface $data_helper;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        DataHelperInterface $data_helper
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary);
        $this->data_helper = $data_helper;
    }

    protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        SlotIdentifier $conditional_slot = SlotIdentifier::NULL
    ): FormInput {
        $langs = [];
        foreach ($this->data_helper->getAllLanguages() as $key) {
            $langs[$key] = $this->presenter->data()->language($key);
        }
        $input = $this->ui_factory->select(
            $this->getInputLabelFromElement($this->presenter, $element, $context_element),
            $langs
        );

        return $this->addConstraintsFromElement(
            $this->constraint_dictionary,
            $element,
            $this->addValueFromElement($element, $input)
        );
    }

    public function getInput(
        ElementInterface $element,
        ElementInterface $context_element
    ): FormInput {
        return $this->rawInput($element, $context_element);
    }

    public function getInputInCondition(
        ElementInterface $element,
        ElementInterface $context_element,
        SlotIdentifier $conditional_slot
    ): FormInput {
        return $this->rawInput($element, $context_element);
    }
}
