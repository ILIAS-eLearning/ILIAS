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

namespace ILIAS\MetaData\Settings\Vocabularies;

use ILIAS\MetaData\Presentation\ElementsInterface as ElementsPresentation;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\PresentationInterface as VocabPresentation;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Type as VocabType;
use ILIAS\MetaData\Vocabularies\Slots\HandlerInterface as SlotHandler;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface as Structure;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface as NavigatorFactory;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\ConditionInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;

class Presentation
{
    protected const MAX_PREVIEW_VALUES = 5;

    protected ElementsPresentation $elements_presentation;
    protected PresentationUtilities $presentation_utils;
    protected VocabPresentation $vocab_presentation;
    protected SlotHandler $slot_handler;
    protected Structure $structure;
    protected NavigatorFactory $navigator_factory;
    protected PathFactory $path_factory;

    /**
     * @var VocabularyInterface[]
     */
    protected array $vocabs;

    public function __construct(
        ElementsPresentation $elements_presentation,
        PresentationUtilities $presentation_utils,
        VocabPresentation $vocab_presentation,
        SlotHandler $slot_handler,
        Structure $structure,
        NavigatorFactory $navigator_factory,
        PathFactory $path_factory
    ) {
        $this->elements_presentation = $elements_presentation;
        $this->presentation_utils = $presentation_utils;
        $this->vocab_presentation = $vocab_presentation;
        $this->slot_handler = $slot_handler;
        $this->structure = $structure;
        $this->navigator_factory = $navigator_factory;
        $this->path_factory = $path_factory;
    }

    public function txt(string $key): string
    {
        return $this->presentation_utils->txt($key);
    }

    public function txtFill(string $key, string ...$values): string
    {
        return $this->presentation_utils->txtFill($key, ...$values);
    }

    public function makeTypePresentable(VocabType $type): string
    {
        return match ($type) {
            VocabType::STANDARD => $this->presentation_utils->txt('md_vocab_type_standard'),
            VocabType::CONTROLLED_STRING => $this->presentation_utils->txt('md_vocab_type_controlled_string'),
            VocabType::CONTROLLED_VOCAB_VALUE => $this->presentation_utils->txt('md_vocab_type_controlled_vocab_value'),
            VocabType::COPYRIGHT => $this->presentation_utils->txt('md_vocab_type_copyright'),
            default => '',
        };
    }

    public function makeSlotPresentable(SlotIdentifier $slot): string
    {
        //skip the name of the element if it does not add any information
        $skip_data = ['string', 'value'];

        $element = $this->getStructureElementFromPath(
            $this->slot_handler->pathForSlot($slot),
            $this->structure->getRoot()
        );
        $element_name = $this->elements_presentation->nameWithParents(
            $element,
            null,
            false,
            in_array($element->getDefinition()->name(), $skip_data),
        );

        if (!$this->slot_handler->isSlotConditional($slot)) {
            return $element_name;
        }

        $condition = $this->slot_handler->conditionForSlot($slot);

        $condition_element = $this->getStructureElementFromPath(
            $condition->path(),
            $element
        );
        $condition_element_name = $this->elements_presentation->nameWithParents(
            $condition_element,
            $this->findFirstCommonParent($element, $condition_element)->getSuperElement(),
            false,
            in_array($element->getDefinition()->name(), $skip_data),
        );

        return $this->presentation_utils->txtFill(
            'md_vocab_element_with_condition',
            $element_name,
            $condition_element_name,
            $this->translateConditionValue($condition, $element)
        );
    }

    /**
     * @return string[]
     */
    public function makeValuesPresentable(
        VocabularyInterface $vocabulary,
        ?int $limit = null
    ): array {
        $presentable_values = [];
        $i = 0;

        $labelled_values = $this->vocab_presentation->labelsForVocabulary(
            $this->presentation_utils,
            $vocabulary
        );
        foreach ($labelled_values as $labelled_value) {
            if ($limit !== null && $i >= $limit) {
                $presentable_values[] = '...';
                break;
            }
            $i++;

            if (
                $vocabulary->type() === VocabType::STANDARD ||
                $vocabulary->type() === VocabType::COPYRIGHT
            ) {
                $presentable_values[] = $labelled_value->label();
                continue;
            }

            $presentable_value = $labelled_value->value();
            if ($labelled_value->label() !== '') {
                $presentable_value = $labelled_value->label() . ' (' . $presentable_value . ')';
            }
            $presentable_values[] = $presentable_value;
        }

        return $presentable_values;
    }

    protected function translateConditionValue(
        ConditionInterface $condition,
        StructureElementInterface $element
    ): string {
        $path_from_root = $this->path_factory->toElement(
            $this->getStructureElementFromPath($condition->path(), $element),
        );
        $slot = $this->slot_handler->identiferFromPathAndCondition($path_from_root, null, null);
        return (string) $this->vocab_presentation->presentableLabels(
            $this->presentation_utils,
            $slot,
            false,
            $condition->value()
        )->current()?->label();
    }

    protected function findFirstCommonParent(
        StructureElementInterface $a,
        StructureElementInterface $b
    ): StructureElementInterface {
        $a_supers = [];
        while ($a) {
            $a_supers[] = $a;
            $a = $a->getSuperElement();
        }
        while ($b) {
            if (in_array($b, $a_supers, true)) {
                return $b;
            }
            $b = $b->getSuperElement();
        }
        return $a;
    }

    protected function getStructureElementFromPath(
        PathInterface $path,
        StructureElementInterface $start
    ): StructureElementInterface {
        return $this->navigator_factory->structureNavigator(
            $path,
            $start
        )->elementAtFinalStep();
    }
}
