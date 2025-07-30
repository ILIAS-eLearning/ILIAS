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

namespace ILIAS\MetaData\Editor\Vocabulary;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Dispatch\ReaderInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\NullReader;
use ILIAS\MetaData\Vocabularies\Slots\ElementHelperInterface;
use ILIAS\MetaData\Vocabularies\Slots\NullElementHelper;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\NullElement;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;

class AdapterTest extends TestCase
{
    protected static function getMockVocabulary(
        bool $allows_custom_inputs = false,
        string $source = '',
        string ...$values
    ): VocabularyInterface {
        return new class ($allows_custom_inputs, $source, $values) extends NullVocabulary {
            public function __construct(
                protected bool $allows_custom_inputs,
                protected string $source,
                protected array $values
            ) {
            }

            public function allowsCustomInputs(): bool
            {
                return $this->allows_custom_inputs;
            }

            public function source(): string
            {
                return $this->source;
            }

            public function values(): \Generator
            {
                yield from $this->values;
            }
        };
    }

    protected function getMockReader(
        Identifier $expected_slot = Identifier::NULL,
        VocabularyInterface ...$returned_vocabularies
    ): ReaderInterface {
        return new class ($expected_slot, $returned_vocabularies) extends NullReader {
            public function __construct(
                protected Identifier $expected_slot,
                protected array $returned_vocabularies
            ) {
            }

            public function activeVocabulariesForSlots(Identifier ...$slots): \Generator
            {
                if (count($slots) !== 1 && $slots[0] !== $this->expected_slot) {
                    throw new \Exception('invalid slot!');
                }
                yield from $this->returned_vocabularies;
            }
        };
    }

    protected function getMockElementHelper(): ElementHelperInterface
    {
        return new class () extends NullElementHelper {
            public array $find_element_of_condition_args = [];
            public array $slot_for_element_args = [];
            public array $slots_for_element_without_condition_args = [];
            public array $potential_slot_for_element_by_condition_args = [];

            public function findElementOfCondition(
                Identifier $slot,
                ElementInterface $element,
                ElementInterface ...$all_elements
            ): ?ElementInterface {
                $this->find_element_of_condition_args[] = [
                    'slot' => $slot,
                    'element' => $element,
                    'all_elements' => $all_elements
                ];
                return null;
            }

            public function slotForElement(ElementInterface $element): Identifier
            {
                $this->slot_for_element_args[] = [
                    'element' => $element
                ];
                return Identifier::NULL;
            }

            public function slotsForElementWithoutCondition(ElementInterface $element): \Generator
            {
                $this->slots_for_element_without_condition_args[] = [
                    'element' => $element
                ];
                yield from [];
            }

            public function potentialSlotForElementByCondition(
                ElementInterface $element,
                ElementInterface $element_in_condition,
                string $value
            ): Identifier {
                $this->potential_slot_for_element_by_condition_args[] = [
                    'element' => $element,
                    'element_in_condition' => $element_in_condition,
                    'value' => $value
                ];
                return Identifier::NULL;
            }
        };
    }

    public function testFindElementOfCondition(): void
    {
        $adapter = new Adapter(
            $this->getMockReader(),
            $helper = $this->getMockElementHelper()
        );
        $element = new NullElement();
        $all_elements = [new NullElement(), new NullElement(), new NullElement()];

        $adapter->findElementOfCondition(
            Identifier::EDCUCATIONAL_INTENDED_END_USER_ROLE,
            $element,
            ...$all_elements
        );

        $this->assertSame(
            [[
                'slot' => Identifier::EDCUCATIONAL_INTENDED_END_USER_ROLE,
                'element' => $element,
                'all_elements' => $all_elements
             ]],
            $helper->find_element_of_condition_args
        );
    }

    public function testSlotForElement(): void
    {
        $adapter = new Adapter(
            $this->getMockReader(),
            $helper = $this->getMockElementHelper()
        );
        $element = new NullElement();

        $adapter->slotForElement($element);

        $this->assertSame(
            [['element' => $element]],
            $helper->slot_for_element_args
        );
    }

    public function testSlotsForElementWithoutCondition(): void
    {
        $adapter = new Adapter(
            $this->getMockReader(),
            $helper = $this->getMockElementHelper()
        );
        $element = new NullElement();

        iterator_to_array($adapter->slotsForElementWithoutCondition($element));

        $this->assertSame(
            [['element' => $element]],
            $helper->slots_for_element_without_condition_args
        );
    }

    public function testPotentialSlotForElementByCondition(): void
    {
        $adapter = new Adapter(
            $this->getMockReader(),
            $helper = $this->getMockElementHelper()
        );
        $element = new NullElement();
        $element_in_condition = new NullElement();

        $adapter->potentialSlotForElementByCondition(
            $element,
            $element_in_condition,
            'some value'
        );

        $this->assertSame(
            [[
                 'element' => $element,
                 'element_in_condition' => $element_in_condition,
                 'value' => 'some value'
             ]],
            $helper->potential_slot_for_element_by_condition_args
        );
    }

    public static function doesSlotHaveVocabulariesProvider(): array
    {
        return [
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [],
                false
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary()],
                true
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(),
                    self::getMockVocabulary(),
                    self::getMockVocabulary(),
                    self::getMockVocabulary()
                ],
                true
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('doesSlotHaveVocabulariesProvider')]
    public function testDoesSlotHaveVocabularies(
        Identifier $slot,
        array $vocabularies,
        bool $has_vocabs_expected
    ): void {
        $adapter = new Adapter(
            $this->getMockReader($slot, ...$vocabularies),
            $this->getMockElementHelper()
        );

        $has_vocabs_actual = $adapter->doesSlotHaveVocabularies($slot);

        $this->assertSame($has_vocabs_expected, $has_vocabs_actual);
    }

    public static function doesSlotAllowCustomInputProvider(): array
    {
        return [
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [],
                true
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(true)],
                true
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(false)],
                false
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(true),
                    self::getMockVocabulary(true),
                    self::getMockVocabulary(false),
                    self::getMockVocabulary(true)
                ],
                false
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('doesSlotAllowCustomInputProvider')]
    public function testDoesSlotAllowCustomInput(
        Identifier $slot,
        array $vocabularies,
        bool $allows_custom_input_expected
    ): void {
        $adapter = new Adapter(
            $this->getMockReader($slot, ...$vocabularies),
            $this->getMockElementHelper()
        );

        $allows_custom_input_actual = $adapter->doesSlotAllowCustomInput($slot);

        $this->assertSame($allows_custom_input_expected, $allows_custom_input_actual);
    }

    public static function isValueInVocabulariesForSlotProvider(): array
    {
        return [
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [],
                'some value',
                false
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(false, '')],
                'some value',
                false
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(false, '', 'val1', 'val2')],
                'some value',
                false
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(false, '', 'val1', 'some value', 'val2')],
                'some value',
                true
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(false, '', 'val1', 'val2'),
                    self::getMockVocabulary(false, '', 'val3', 'val4')
                ],
                'some value',
                false
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(false, '', 'val1', 'val2'),
                    self::getMockVocabulary(false, '', 'val3', 'some value', 'val4')
                ],
                'some value',
                true
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(false, '', 'some value', 'some value'),
                    self::getMockVocabulary(false, '', 'some value', 'some value', 'some value')
                ],
                'some value',
                true
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValueInVocabulariesForSlotProvider')]
    public function testIsValueInVocabulariesForSlot(
        Identifier $slot,
        array $vocabularies,
        string $value,
        bool $is_in_vocabs_expected
    ): void {
        $adapter = new Adapter(
            $this->getMockReader($slot, ...$vocabularies),
            $this->getMockElementHelper()
        );

        $is_in_vocabs_actual = $adapter->isValueInVocabulariesForSlot($slot, $value);

        $this->assertSame($is_in_vocabs_expected, $is_in_vocabs_actual);
    }

    public static function valuesInVocabulariesForSlotProvider(): array
    {
        return [
            [
                Identifier::EDUCATIONAL_CONTEXT,
                null,
                [],
                []
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                'additional value',
                [],
                ['additional value']
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                null,
                [self::getMockVocabulary(false, '')],
                []
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                null,
                [self::getMockVocabulary(false, '', 'val1')],
                ['val1']
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                'val1',
                [self::getMockVocabulary(false, '', 'val1')],
                ['val1']
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                'additional value',
                [self::getMockVocabulary(false, '', 'val1')],
                ['additional value', 'val1']
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                null,
                [self::getMockVocabulary(false, '', 'val1', 'val2')],
                ['val1', 'val2']
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                null,
                [
                    self::getMockVocabulary(false, '', 'val1', 'val2'),
                    self::getMockVocabulary(false, '', 'val3', 'val4')
                ],
                ['val1', 'val2', 'val3', 'val4']
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                'val3',
                [
                    self::getMockVocabulary(false, '', 'val1', 'val2'),
                    self::getMockVocabulary(false, '', 'val3'),
                    self::getMockVocabulary(false, '', 'val4', 'val5')
                ],
                ['val1', 'val2', 'val3', 'val4', 'val5']
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                'additional value',
                [
                    self::getMockVocabulary(false, '', 'val1', 'val2'),
                    self::getMockVocabulary(false, '', 'val3'),
                    self::getMockVocabulary(false, '', 'val4', 'val5')
                ],
                ['additional value', 'val1', 'val2', 'val3', 'val4', 'val5']
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('valuesInVocabulariesForSlotProvider')]
    public function testValuesInVocabulariesForSlot(
        Identifier $slot,
        ?string $add_if_not_included,
        array $vocabularies,
        array $expected_values
    ): void {
        $adapter = new Adapter(
            $this->getMockReader($slot, ...$vocabularies),
            $this->getMockElementHelper()
        );

        $actual_values = iterator_to_array($adapter->valuesInVocabulariesForSlot($slot, $add_if_not_included));

        $this->assertSame($expected_values, $actual_values);
    }

    public static function sourceMapForSlotProvider(): array
    {
        return [
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [],
                'some value',
                null
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(false, '')],
                'some value',
                null
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(false, 'some source', 'some value')],
                'some value',
                'some source'
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [self::getMockVocabulary(false, 'some source', 'some value')],
                'other value',
                null
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(false, 'source 1', 'val1.1', 'val1.2'),
                    self::getMockVocabulary(false, 'source 2', 'val2.1', 'val2.2')
                ],
                'val1.2',
                'source 1'
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(false, 'source 1', 'val1.1', 'val1.2'),
                    self::getMockVocabulary(false, 'source 2', 'val2.1', 'val2.2')
                ],
                'val2.1',
                'source 2'
            ],
            [
                Identifier::EDUCATIONAL_CONTEXT,
                [
                    self::getMockVocabulary(false, 'source 1', 'val1.1', 'val1.2'),
                    self::getMockVocabulary(false, 'source 2', 'val2.1', 'val2.2')
                ],
                'other value',
                null
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('sourceMapForSlotProvider')]
    public function testSourceMapForSlot(
        Identifier $slot,
        array $vocabularies,
        string $value,
        ?string $expected_source
    ): void {
        $adapter = new Adapter(
            $this->getMockReader($slot, ...$vocabularies),
            $this->getMockElementHelper()
        );

        $closure = $adapter->sourceMapForSlot($slot);
        $actual_source = $closure($value);

        $this->assertSame($expected_source, $actual_source);
    }
}
