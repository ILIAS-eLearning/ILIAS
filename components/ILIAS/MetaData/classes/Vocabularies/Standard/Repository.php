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

namespace ILIAS\MetaData\Vocabularies\Standard;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValueInterface;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValue;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface;
use ILIAS\MetaData\Vocabularies\Standard\Assignment\AssignmentsInterface;

class Repository implements RepositoryInterface
{
    protected GatewayInterface $gateway;
    protected FactoryInterface $factory;
    protected AssignmentsInterface $assignments;

    public function __construct(
        GatewayInterface $gateway,
        FactoryInterface $factory,
        AssignmentsInterface $assignments
    ) {
        $this->gateway = $gateway;
        $this->factory = $factory;
        $this->assignments = $assignments;
    }

    public function deactivateVocabulary(SlotIdentifier $slot): void
    {
        if (!$this->isSlotValid($slot)) {
            return;
        }

        if (!$this->gateway->doesDeactivationEntryExistForSlot($slot)) {
            $this->gateway->createDeactivationEntry($slot);
        }
    }

    public function activateVocabulary(SlotIdentifier $slot): void
    {
        if (!$this->isSlotValid($slot)) {
            return;
        }

        $this->gateway->deleteDeactivationEntry($slot);
    }

    public function isVocabularyActive(SlotIdentifier $slot): bool
    {
        return !$this->gateway->doesDeactivationEntryExistForSlot($slot);
    }

    public function getVocabulary(SlotIdentifier $slot): VocabularyInterface
    {
        if (!$this->assignments->doesSlotHaveValues($slot)) {
            return $this->factory->null();
        }
        return $this->factory->standard(
            $slot,
            ...$this->assignments->valuesForSlot($slot)
        )->withIsDeactivated(!$this->isVocabularyActive($slot))->get();
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabularies(SlotIdentifier ...$slots): \Generator
    {
        foreach ($slots as $slot) {
            if (!$this->assignments->doesSlotHaveValues($slot)) {
                continue;
            }
            yield $this->factory->standard(
                $slot,
                ...$this->assignments->valuesForSlot($slot)
            )->withIsDeactivated(!$this->isVocabularyActive($slot))->get();
        }
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getActiveVocabularies(SlotIdentifier ...$slots): \Generator
    {
        foreach ($slots as $slot) {
            if (
                !$this->assignments->doesSlotHaveValues($slot) ||
                !$this->isVocabularyActive($slot)
            ) {
                continue;
            }
            yield $this->factory->standard(
                $slot,
                ...$this->assignments->valuesForSlot($slot)
            )->get();
        }
    }

    /**
     * @return LabelledValueInterface[]
     */
    public function getLabelsForValues(
        PresentationUtilities $presentation_utilities,
        SlotIdentifier $slot,
        bool $only_active,
        string ...$values
    ): \Generator {
        if ($only_active) {
            $vocabularies = $this->getActiveVocabularies($slot);
        } else {
            $vocabularies = $this->getVocabularies($slot);
        }

        $vocab_values = [];
        foreach ($vocabularies as $vocabulary) {
            $vocab_values = array_merge(
                $vocab_values,
                iterator_to_array($vocabulary->values())
            );
        }

        foreach ($values as $value) {
            if (!in_array($value, $vocab_values, true)) {
                continue;
            }
            yield new LabelledValue(
                $value,
                $this->getLabelForValue($presentation_utilities, $value)
            );
        }
    }

    protected function getLabelForValue(
        PresentationUtilities $presentation_utilities,
        string $value
    ): string {
        $value = $this->camelCaseToSpaces($value);
        $exceptions = [
            'ispartof' => 'is_part_of', 'haspart' => 'has_part',
            'isversionof' => 'is_version_of', 'hasversion' => 'has_version',
            'isformatof' => 'is_format_of', 'hasformat' => 'has_format',
            'references' => 'references',
            'isreferencedby' => 'is_referenced_by',
            'isbasedon' => 'is_based_on', 'isbasisfor' => 'is_basis_for',
            'requires' => 'requires', 'isrequiredby' => 'is_required_by',
            'graphical designer' => 'graphicaldesigner',
            'technical implementer' => 'technicalimplementer',
            'content provider' => 'contentprovider',
            'technical validator' => 'technicalvalidator',
            'educational validator' => 'educationalvalidator',
            'script writer' => 'scriptwriter',
            'instructional designer' => 'instructionaldesigner',
            'subject matter expert' => 'subjectmatterexpert',
            'diagram' => 'diagramm'
        ];
        if (array_key_exists($value, $exceptions)) {
            $value = $exceptions[$value];
        }

        return $presentation_utilities->txt('meta_' . $this->fillSpaces($value));
    }

    protected function camelCaseToSpaces(string $string): string
    {
        $string = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $string);
        return strtolower($string);
    }

    protected function fillSpaces(string $string): string
    {
        $string = str_replace(' ', '_', $string);
        return strtolower($string);
    }

    protected function isSlotValid(SlotIdentifier $slot): bool
    {
        $valid_slots = [
            SlotIdentifier::GENERAL_STRUCTURE,
            SlotIdentifier::GENERAL_AGGREGATION_LEVEL,
            SlotIdentifier::LIFECYCLE_STATUS,
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_ROLE,
            SlotIdentifier::METAMETADATA_CONTRIBUTE_ROLE,
            SlotIdentifier::TECHNICAL_REQUIREMENT_TYPE,
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
            SlotIdentifier::TECHNICAL_REQUIREMENT_OS,
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE,
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE,
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL,
            SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY,
            SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE,
            SlotIdentifier::EDUCATIONAL_CONTEXT,
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::RIGHTS_COST,
            SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS,
            SlotIdentifier::RELATION_KIND,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];

        return in_array($slot, $valid_slots, true);
    }
}
