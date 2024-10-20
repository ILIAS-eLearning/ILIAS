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
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Vocabularies\ElementHelper\ElementHelperInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\Paths\PathInterface;

class VocabValueFactory extends BaseFactory
{
    protected ElementHelperInterface $element_vocab_helper;
    protected Refinery $refinery;
    protected PathFactory $path_factory;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        ElementHelperInterface $element_vocab_helper,
        Refinery $refinery,
        PathFactory $path_factory
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary);
        $this->element_vocab_helper = $element_vocab_helper;
        $this->refinery = $refinery;
        $this->path_factory = $path_factory;
    }

    protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        SlotIdentifier $slot,
        bool $add_value_from_data
    ): FormInput {
        $data = null;
        if ($element->getData()->type() !== Type::NULL) {
            $data = $element->getData()->value();
        }

        $raw_values = [];
        $sources_by_value = [];
        foreach ($this->element_vocab_helper->vocabulariesForSlot($slot) as $vocab) {
            $values_from_vocab = iterator_to_array($vocab->values());

            $raw_values = array_merge($raw_values, $values_from_vocab);
            $sources_by_value = array_merge(
                $sources_by_value,
                array_fill_keys($values_from_vocab, $vocab->source())
            );
        }
        if ($add_value_from_data && isset($data) && !in_array($data, $raw_values)) {
            array_unshift($raw_values, $data);
        }

        $values = [];
        foreach ($this->presenter->data()->vocabularyValues($slot, ...$raw_values) as $labelled_value) {
            $values[$labelled_value->value()] = $labelled_value->label();
        }

        $input = $this->ui_factory->select(
            $this->getInputLabelFromElement($this->presenter, $element, $context_element),
            $values
        );
        if ($add_value_from_data && isset($data)) {
            $input = $input->withValue($data);
        }

        $source_path = $this->getPathToSourceElement($element);
        return $this->addConstraintsFromElement($this->constraint_dictionary, $element, $input)
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(function ($vs) use ($sources_by_value, $source_path) {
                            $source = $sources_by_value[$vs] ?? null;
                            return [
                                $vs,
                                [$source_path->toString() => $source]
                            ];
                        })
                    );
    }

    public function getInput(
        ElementInterface $element,
        ElementInterface $context_element
    ): FormInput {
        return $this->rawInput(
            $element,
            $context_element,
            $slot = $this->element_vocab_helper->slotForElement($element),
            true
        );
    }

    public function getInputInCondition(
        ElementInterface $element,
        ElementInterface $context_element,
        SlotIdentifier $conditional_slot
    ): FormInput {
        $slot = $this->element_vocab_helper->slotForElement($element);
        return $this->rawInput(
            $element,
            $context_element,
            $conditional_slot,
            $slot === $conditional_slot
        );
    }

    public function getPathToSourceElement(ElementInterface $element): PathInterface
    {
        foreach ($element->getSuperElement()->getSubElements() as $el) {
            if ($el->getDefinition()->dataType() === Type::VOCAB_SOURCE) {
                return $this->path_factory->toElement($el, true);
            }
        }
        throw new \ilMDEditorException('Vocab values must not be separated from their source.');
    }
}
