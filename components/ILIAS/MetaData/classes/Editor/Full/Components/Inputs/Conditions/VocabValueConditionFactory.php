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

namespace ILIAS\MetaData\Editor\Full\Components\Inputs\Conditions;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Full\Components\Inputs\WithoutConditions\FactoryWithoutConditionTypesService;
use ILIAS\MetaData\Editor\Vocabulary\AdapterInterface as VocabularyAdapter;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class VocabValueConditionFactory extends BaseConditionFactory
{
    protected PathFactory $path_factory;
    protected Refinery $refinery;
    protected VocabularyAdapter $vocabulary_adapter;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        FactoryWithoutConditionTypesService $types,
        PathFactory $path_factory,
        Refinery $refinery,
        VocabularyAdapter $vocabulary_adapter
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary, $types);
        $this->path_factory = $path_factory;
        $this->refinery = $refinery;
        $this->vocabulary_adapter = $vocabulary_adapter;
    }

    public function getConditionInput(
        ElementInterface $element,
        ElementInterface $context_element,
        ElementInterface $conditional_element
    ): FormInput {
        $slot = $this->vocabulary_adapter->slotForElement($element);
        $unique_path_to_conditional_element = $this->path_factory->toElement($conditional_element, true);

        $data = $this->getDataFromElementOrConstraint($element);
        $conditional_data = $this->getDataFromElementOrConstraint($conditional_element);

        $groups = [];
        $values = iterator_to_array($this->vocabulary_adapter->valuesInVocabulariesForSlot($slot));
        $labels_by_value = $this->getLabelsByValueForVocabulary($slot, ...$values);
        foreach ($values as $value) {
            $conditional_slot = $this->vocabulary_adapter->potentialSlotForElementByCondition(
                $conditional_element,
                $element,
                $value
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

        $radios = $this->ui_factory->switchableGroup(
            $groups,
            $this->getInputLabelFromElement($this->presenter, $element, $context_element)
        );
        if (isset($data)) {
            $radios = $radios->withValue($data);
        }

        $source_map = $this->vocabulary_adapter->sourceMapForSlot($slot);
        $source_path = $this->getPathToSourceElement($element);
        return $this->addConstraintsFromElement(
            $this->constraint_dictionary,
            $element,
            $radios
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($vs) use ($source_map, $source_path) {
                $source = $source_map((string) $vs[0]);
                $vs[1][$source_path->toString()] = $source;
                return $vs;
            })
        );
    }

    protected function getLabelsByValueForVocabulary(SlotIdentifier $identifier, string ...$values): array
    {
        $presentable_labels = $this->presenter->data()->vocabularyValues(
            $identifier,
            ...$values
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
