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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Elements\Data\Type;

class NonNegIntFactory extends BaseFactory
{
    protected Refinery $refinery;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        Refinery $refinery
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary);
        $this->refinery = $refinery;
    }

    protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        SlotIdentifier $conditional_slot = SlotIdentifier::NULL
    ): FormInput {
        $input = $this->ui_factory
            ->numeric($this->getInputLabelFromElement($this->presenter, $element, $context_element))
            ->withAdditionalTransformation(
                $this->refinery->int()->isGreaterThanOrEqual(0)
            )
            ->withAdditionalTransformation(
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->string(),
                    $this->refinery->kindlyTo()->null()
                ])
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
