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

namespace ILIAS\MetaData\Editor\Full\Services\Inputs\Conditions;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Full\Services\Inputs\WithoutConditions\FactoryWithoutConditionTypesService;
use ILIAS\MetaData\Vocabularies\ElementHelper\ElementHelperInterface;
use ILIAS\MetaData\Vocabularies\Slots\HandlerInterface as VocabSlotHandler;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;

class VocabValueConditionFactory extends BaseConditionFactory
{
    protected PathFactory $path_factory;
    protected ElementHelperInterface $element_vocab_helper;
    protected VocabSlotHandler $vocab_slot_handler;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        FactoryWithoutConditionTypesService $types,
        PathFactory $path_factory,
        ElementHelperInterface $element_vocab_helper,
        VocabSlotHandler $vocab_slot_handler
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary, $types);
        $this->path_factory = $path_factory;
        $this->element_vocab_helper = $element_vocab_helper;
        $this->vocab_slot_handler = $vocab_slot_handler;
    }

    public function getConditionInput(
        ElementInterface $element,
        ElementInterface $context_element,
        ElementInterface $conditional_element
    ): FormInput {
        $slot = $this->element_vocab_helper->slotForElement($element);
        $unique_path_to_conditional_element = $this->path_factory->toElement($conditional_element, true);
        $path_for_conditional_slot = $this->path_factory->toElement($conditional_element);
        $path_for_condition = $this->path_factory->betweenElements($conditional_element, $element);

        $data = $this->getDataFromElementOrConstraint($element);
        $conditional_data = $this->getDataFromElementOrConstraint($conditional_element);

        $groups = [];
        foreach ($this->element_vocab_helper->vocabulariesForSlot($slot) as $vocab) {
            $labels_by_value = $this->getLabelsByValueForVocabulary($vocab);
            foreach ($vocab->values() as $value) {
                $conditional_slot = $this->vocab_slot_handler->identiferFromPathAndCondition(
                    $path_for_conditional_slot,
                    $path_for_condition,
                    $value,
                );

                $input = $this->getInputInCondition(
                    $conditional_element,
                    $context_element,
                    $conditional_slot
                );

                if ($data === $value && isset($conditional_data)) {
                    $input = $input->withValue($conditional_data);
                }

                $groups[$value] = $this->ui_factory->group(
                    [$unique_path_to_conditional_element->toString() => $input],
                    $labels_by_value[$value] ?? ''
                );
            }
        }

        $radios = $this->ui_factory->switchableGroup(
            $groups,
            $this->getInputLabelFromElement($this->presenter, $element, $context_element)
        );
        if (isset($data)) {
            $radios = $radios->withValue($data);
        }
        return $this->addConstraintsFromElement(
            $this->constraint_dictionary,
            $element,
            $radios
        );
    }

    protected function getLabelsByValueForVocabulary(VocabularyInterface $vocabulary): array
    {
        $presentable_labels = $this->presenter->data()->vocabularyValues(
            $vocabulary->slot(),
            ...$vocabulary->values()
        );
        $labels_by_value = [];
        foreach ($presentable_labels as $labelled_value) {
            $labels_by_value[$labelled_value->value()] = $labelled_value->label();
        }
        return $labels_by_value;
    }

    protected function getDataFromElementOrConstraint(ElementInterface $element): ?string
    {
        $data = null;
        if ($element->getData()->type() !== Type::NULL) {
            $data = $element->getData()->value();
        }
        return $this->getPresetValueFromConstraints($this->constraint_dictionary, $element) ?? $data;
    }
}
