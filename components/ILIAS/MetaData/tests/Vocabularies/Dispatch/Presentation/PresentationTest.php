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

namespace ILIAS\MetaData\Vocabularies\Dispatch\Presentation;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Presentation\NullUtilities;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepository;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepository;
use ILIAS\MetaData\Vocabularies\Copyright\NullBridge as NullCopyrightBridge;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository as NullControlledRepository;
use ILIAS\MetaData\Vocabularies\Standard\NullRepository as NullStandardRepository;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;

class PresentationTest extends TestCase
{
    public function getVocabulary(
        Type $type,
        SlotIdentifier $slot,
        string ...$values
    ): VocabularyInterface {
        return new class ($type, $slot, $values) extends NullVocabulary {
            public function __construct(
                protected Type $type,
                protected SlotIdentifier $slot,
                protected array $values
            ) {
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function slot(): SlotIdentifier
            {
                return $this->slot;
            }

            public function values(): \Generator
            {
                yield from $this->values;
            }
        };
    }

    public function getPresentationUtilities(): PresentationUtilities
    {
        return new class () extends NullUtilities {
            public function txt(string $key): string
            {
                return 'translated suffix';
            }
        };
    }

    public function getCopyrightBridge(
        SlotIdentifier $slot_with_vocab,
        string ...$values_from_vocab
    ): CopyrightBridge {
        return new class ($slot_with_vocab, $values_from_vocab) extends NullCopyrightBridge {
            public function __construct(
                protected SlotIdentifier $slot_with_vocab,
                protected array $values_from_vocab
            ) {
            }

            public function labelsForValues(
                SlotIdentifier $slot,
                string ...$values
            ): \Generator {
                foreach ($values as $value) {
                    if ($slot !== $this->slot_with_vocab) {
                        return;
                    }
                    if (!in_array($value, $this->values_from_vocab)) {
                        continue;
                    }
                    yield new class ($value) extends NullLabelledValue {
                        public function __construct(protected string $value)
                        {
                        }

                        public function value(): string
                        {
                            return $this->value;
                        }

                        public function label(): string
                        {
                            return 'copyright label for ' . $this->value;
                        }
                    };
                }
            }
        };
    }

    public function getControlledRepository(
        SlotIdentifier $slot_with_vocab,
        bool $only_active,
        bool $empty_labels,
        string ...$values_from_vocab
    ): ControlledRepository {
        return new class ($slot_with_vocab, $only_active, $empty_labels, $values_from_vocab) extends NullControlledRepository {
            public function __construct(
                protected SlotIdentifier $slot_with_vocab,
                protected bool $only_active,
                protected bool $empty_labels,
                protected array $values_from_vocab
            ) {
            }

            public function getLabelsForValues(
                SlotIdentifier $slot,
                bool $only_active,
                string ...$values
            ): \Generator {
                if ($slot !== $this->slot_with_vocab || $only_active !== $this->only_active) {
                    return;
                }
                foreach ($values as $value) {
                    if (!in_array($value, $this->values_from_vocab)) {
                        continue;
                    }
                    yield new class ($value, $this->empty_labels) extends NullLabelledValue {
                        public function __construct(
                            protected string $value,
                            protected bool $empty_label
                        ) {
                        }

                        public function value(): string
                        {
                            return $this->value;
                        }

                        public function label(): string
                        {
                            if ($this->empty_label) {
                                return '';
                            }
                            return 'controlled label for ' . $this->value;
                        }
                    };
                }
            }
        };
    }

    public function getStandardRepository(
        SlotIdentifier $slot_with_vocab,
        bool $only_active,
        string ...$values_from_vocab
    ): StandardRepository {
        return new class ($slot_with_vocab, $only_active, $values_from_vocab) extends NullStandardRepository {
            public function __construct(
                protected SlotIdentifier $slot_with_vocab,
                protected bool $only_active,
                protected array $values_from_vocab
            ) {
            }

            public function getLabelsForValues(
                PresentationUtilities $presentation_utilities,
                SlotIdentifier $slot,
                bool $only_active,
                string ...$values
            ): \Generator {
                if ($slot !== $this->slot_with_vocab || $only_active !== $this->only_active) {
                    return;
                }
                foreach ($values as $value) {
                    if (!in_array($value, $this->values_from_vocab)) {
                        continue;
                    }
                    yield new class ($value) extends NullLabelledValue {
                        public function __construct(protected string $value)
                        {
                        }

                        public function value(): string
                        {
                            return $this->value;
                        }

                        public function label(): string
                        {
                            return 'standard label for ' . $this->value;
                        }
                    };
                }
            }
        };
    }

    protected function assertLabelledValueMatches(
        LabelledValueInterface $labelled_value,
        string $expected_value,
        string $expected_label
    ): void {
        $this->assertSame(
            $expected_value,
            $labelled_value->value(),
            'Value of Labelled value ' . $labelled_value->value() . ' should be ' . $expected_value
        );
        $this->assertSame(
            $expected_label,
            $labelled_value->label(),
            'Label of Labelled value ' . $labelled_value->label() . ' should be ' . $expected_label
        );
    }

    /**
     * @param LabelledValueInterface $labelled_values
     * @param array $expected with each expected labelledValue as ['value' => $value, 'label' => $label]
     */
    protected function assertLabelledValuesMatchInOrder(
        \Generator $labelled_values,
        array $expected
    ): void {
        $as_array = iterator_to_array($labelled_values);
        $this->assertCount(
            count($expected),
            $as_array,
            'There should be ' . count($expected) . ' labelled values, not ' . count($as_array)
        );
        $i = 0;
        foreach ($as_array as $labelled_value) {
            $this->assertLabelledValueMatches(
                $labelled_value,
                $expected[$i]['value'],
                $expected[$i]['label']
            );
            $i++;
        }
    }

    public function singleValueProvider(): array
    {
        return [
            ['v1', true, true, true, 'copyright label for v1'],
            ['v2', true, false, true, 'copyright label for v2'],
            ['v3', true, true, false, 'copyright label for v3'],
            ['v4', true, false, false, 'copyright label for v4'],
            ['v5', false, true, true, 'controlled label for v5'],
            ['v6', false, false, true, 'standard label for v6'],
            ['v7', false, true, false, 'controlled label for v7'],
            ['v8', false, false, false, 'v8']
        ];
    }

    /**
     * @dataProvider singleValueProvider
     */
    public function testPresentableLabels(
        string $value,
        bool $is_in_copyright,
        bool $is_in_controlled,
        bool $is_in_standard,
        string $expected_label
    ): void {
        $presentation = new Presentation(
            $this->getCopyrightBridge(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                ...($is_in_copyright ? [$value] : [])
            ),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true,
                false,
                ...($is_in_controlled ? [$value] : [])
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true,
                ...($is_in_standard ? [$value] : [])
            )
        );

        $labels = $presentation->presentableLabels(
            $this->getPresentationUtilities(),
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            false,
            $value
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [['value' => $value, 'label' => $expected_label]]
        );
    }

    /**
     * @dataProvider singleValueProvider
     */
    public function testPresentableLabelsWithUnknownVocab(
        string $value,
        bool $is_in_copyright,
        bool $is_in_controlled,
        bool $is_in_standard,
        string $expected_label_without_suffix
    ): void {
        $presentation = new Presentation(
            $this->getCopyrightBridge(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                ...($is_in_copyright ? [$value] : [])
            ),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true,
                false,
                ...($is_in_controlled ? [$value] : [])
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true,
                ...($is_in_standard ? [$value] : [])
            )
        );

        $labels = $presentation->presentableLabels(
            $this->getPresentationUtilities(),
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            true,
            $value
        );

        $suffix = (!$is_in_standard && !$is_in_controlled && !$is_in_copyright) ? ' translated suffix' : '';
        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [['value' => $value, 'label' => $expected_label_without_suffix . $suffix]]
        );
    }

    public function testPresentableLabelsWithEmptyLabelFromControlledVocabulary(): void
    {
        $value = 'some value';
        $presentation = new Presentation(
            $this->getCopyrightBridge(SlotIdentifier::EDUCATIONAL_DIFFICULTY),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true,
                true,
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true
            )
        );

        $labels = $presentation->presentableLabels(
            $this->getPresentationUtilities(),
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            false,
            $value
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [['value' => $value, 'label' => $value]]
        );
    }

    public function testPresentableLabelsMultipleValues(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'cp 1',
                'cp 2'
            ),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true,
                false,
                'contr 1',
                'contr 2',
                'contr 3'
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                true,
                'stand 1',
                'stand 2'
            )
        );

        $labels = $presentation->presentableLabels(
            $this->getPresentationUtilities(),
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            false,
            'contr 2',
            'cp 1',
            'something else',
            'stand 2',
            'contr 3'
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'contr 2', 'label' => 'controlled label for contr 2'],
                ['value' => 'cp 1', 'label' => 'copyright label for cp 1'],
                ['value' => 'something else', 'label' => 'something else'],
                ['value' => 'stand 2', 'label' => 'standard label for stand 2'],
                ['value' => 'contr 3', 'label' => 'controlled label for contr 3']
            ]
        );
    }

    public function testLabelsForVocabularyStandard(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            ),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                false,
                'v1',
                'v2',
                'v3'
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                'v1',
                'v2',
                'v3'
            )
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::STANDARD,
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'standard label for v1'],
                ['value' => 'v2', 'label' => 'standard label for v2'],
                ['value' => 'v3', 'label' => 'standard label for v3']
            ]
        );
    }

    public function testLabelsForVocabularyControlledString(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            ),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                false,
                'v1',
                'v2',
                'v3'
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                'v1',
                'v2',
                'v3'
            )
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::CONTROLLED_STRING,
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'controlled label for v1'],
                ['value' => 'v2', 'label' => 'controlled label for v2'],
                ['value' => 'v3', 'label' => 'controlled label for v3']
            ]
        );
    }

    public function testLabelsForVocabularyControlledVocabValue(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            ),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                false,
                'v1',
                'v2',
                'v3'
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                'v1',
                'v2',
                'v3'
            )
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::CONTROLLED_VOCAB_VALUE,
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'controlled label for v1'],
                ['value' => 'v2', 'label' => 'controlled label for v2'],
                ['value' => 'v3', 'label' => 'controlled label for v3']
            ]
        );
    }

    public function testLabelsForVocabularyCopyright(): void
    {
        $presentation = new Presentation(
            $this->getCopyrightBridge(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            ),
            $this->getControlledRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                false,
                'v1',
                'v2',
                'v3'
            ),
            $this->getStandardRepository(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                false,
                'v1',
                'v2',
                'v3'
            )
        );

        $labels = $presentation->labelsForVocabulary(
            $this->getPresentationUtilities(),
            $this->getVocabulary(
                Type::COPYRIGHT,
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                'v1',
                'v2',
                'v3'
            )
        );

        $this->assertLabelledValuesMatchInOrder(
            $labels,
            [
                ['value' => 'v1', 'label' => 'copyright label for v1'],
                ['value' => 'v2', 'label' => 'copyright label for v2'],
                ['value' => 'v3', 'label' => 'copyright label for v3']
            ]
        );
    }
}
