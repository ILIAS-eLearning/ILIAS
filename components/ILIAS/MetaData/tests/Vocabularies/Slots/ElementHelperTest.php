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

namespace ILIAS\MetaData\Vocabularies\Slots;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\NullElement;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Paths\NullFactory as NullPathFactory;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\CheckerInterface as ConditionCheckerInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\NullChecker as NullConditionChecker;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NullNavigatorFactory;
use ILIAS\MetaData\Paths\Navigator\NavigatorInterface;
use ILIAS\MetaData\Paths\Navigator\NullNavigator;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\ConditionInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\NullCondition;

class ElementHelperTest extends TestCase
{
    protected static function getMockElement(string $name): ElementInterface
    {
        return new class ($name) extends NullElement {
            public function __construct(
                protected string $name
            ) {
            }

            public function getDefinition(): DefinitionInterface
            {
                return new class ($this->name) extends NullDefinition {
                    public function __construct(
                        protected string $name
                    ) {
                    }

                    public function name(): string
                    {
                        return $this->name;
                    }
                };
            }
        };
    }

    protected function getMockConditionChecker(
        string $element_name = '',
        ?Identifier $fits_slot = null
    ): ConditionCheckerInterface {
        return new class ($element_name, $fits_slot) extends NullConditionChecker {
            public function __construct(
                protected string $element_name,
                protected ?Identifier $fits_slot
            ) {
            }

            public function doesElementFitSlot(
                ElementInterface $element,
                SlotIdentifier $slot,
                bool $ignore_markers = true
            ): bool {
                if ($element->getDefinition()->name() !== $this->element_name) {
                    return false;
                }
                if ($slot !== $this->fits_slot) {
                    return false;
                }
                return true;
            }
        };
    }

    protected function getMockPathFactory(): PathFactoryInterface
    {
        return new class () extends NullPathFactory {
            public function toElement(
                BaseElementInterface $to,
                bool $leads_to_exactly_one = false
            ): PathInterface {
                $string = '';
                if ($leads_to_exactly_one) {
                    $string = 'exactly ';
                }
                $string .= 'to ' . $to->getDefinition()->name();
                return new class ($string) extends NullPath {
                    public function __construct(
                        protected string $string
                    ) {
                    }

                    public function toString(): string
                    {
                        return $this->string;
                    }
                };
            }

            public function betweenElements(
                BaseElementInterface $from,
                BaseElementInterface $to,
                bool $leads_to_exactly_one = false
            ): PathInterface {
                $string = '';
                if ($leads_to_exactly_one) {
                    $string = 'exactly ';
                }
                $string .= 'from ' . $from->getDefinition()->name() . ' to ' . $to->getDefinition()->name();
                return new class ($string) extends NullPath {
                    public function __construct(
                        protected string $string
                    ) {
                    }

                    public function toString(): string
                    {
                        return $this->string;
                    }
                };
            }
        };
    }

    public function getMockNavigatorFactory(
        string $expected_path = '',
        string $expected_start_element_name = '',
        ?ElementInterface $returned_element = null
    ): NavigatorFactoryInterface {
        return new class ($expected_path, $expected_start_element_name, $returned_element) extends NullNavigatorFactory {
            public function __construct(
                protected string $expected_path,
                protected string $expected_start_element_name,
                protected ?ElementInterface $expected_element
            ) {
            }

            public function navigator(PathInterface $path, ElementInterface $start_element): NavigatorInterface
            {
                if (
                    $path->toString() !== $this->expected_path &&
                    $start_element->getDefinition()->name() !== $this->expected_start_element_name
                ) {
                    throw new \Exception('wrong path or element name');
                }
                return new class ($this->expected_element) extends NullNavigator {
                    public function __construct(
                        protected ?ElementInterface $leads_to
                    ) {
                    }

                    public function lastElementAtFinalStep(): ?ElementInterface
                    {
                        return $this->leads_to;
                    }
                };
            }
        };
    }

    public function getMockHandler(
        string $expected_path = '',
        array $identifiers = [],
        array $condition_paths_by_identifer = [],
        array $condition_values_by_identifer = []
    ): HandlerInterface {
        return new class ($expected_path, $identifiers, $condition_paths_by_identifer, $condition_values_by_identifer) extends NullHandler {
            public function __construct(
                protected string $expected_path,
                protected array $identifiers,
                protected array $condition_paths_by_identifer,
                protected array $condition_values_by_identifer
            ) {
            }

            public function allSlotsForPath(PathInterface $path_to_element): \Generator
            {
                if ($path_to_element->toString() !== $this->expected_path) {
                    throw new \Exception('wrong path');
                }
                yield from $this->identifiers;
            }

            public function identiferFromPathAndCondition(
                PathInterface $path_to_element,
                ?PathInterface $path_to_condition,
                ?string $condition_value
            ): Identifier {
                if ($path_to_element->toString() !== $this->expected_path) {
                    throw new \Exception('wrong path');
                }
                foreach ($this->identifiers as $identifier) {
                    if (
                        ($this->condition_paths_by_identifer[$identifier->value] ?? null) !== $path_to_condition->toString() ||
                        ($this->condition_values_by_identifer[$identifier->value] ?? null) !== $condition_value
                    ) {
                        continue;
                    }
                    return $identifier;
                };
                return Identifier::NULL;
            }

            public function isSlotConditional(Identifier $identifier): bool
            {
                return isset($this->condition_paths_by_identifer[$identifier->value]) &&
                    isset($this->condition_values_by_identifer[$identifier->value]);
            }

            public function conditionForSlot(Identifier $identifier): ?ConditionInterface
            {
                $condition_path = $this->condition_paths_by_identifer[$identifier->value] ?? null;
                if ($condition_path === null) {
                    return null;
                }
                // sometimes null
                return new class ($condition_path) extends NullCondition {
                    public function __construct(
                        protected string $condition_path
                    ) {
                    }

                    public function path(): PathInterface
                    {
                        return new class ($this->condition_path) extends NullPath {
                            public function __construct(
                                protected string $string
                            ) {
                            }

                            public function toString(): string
                            {
                                return $this->string;
                            }
                        };
                    }
                };
            }
        };
    }

    public static function slotsForElementWithoutConditionProvider(): array
    {
        return [
            [
                'some element',
                []
            ],
            [
                'some element',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS]
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('slotsForElementWithoutConditionProvider')]
    public function testSlotsForElementWithoutCondition(
        string $element_name,
        array $expected_identifiers
    ): void {
        $helper = new ElementHelper(
            $this->getMockHandler('to ' . $element_name, $expected_identifiers),
            $this->getMockPathFactory(),
            $this->getMockNavigatorFactory(),
            $this->getMockConditionChecker()
        );
        $element = self::getMockElement($element_name);

        $actual_identifiers = iterator_to_array($helper->slotsForElementWithoutCondition($element));

        $this->assertSame($expected_identifiers, $actual_identifiers);
    }

    public static function slotsForElementProvider(): array
    {
        return [
            [
                'some element',
                [],
                null
            ],
            [
                'some element',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS],
                null
            ],
            [
                'some element',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS],
                Identifier::GENERAL_STRUCTURE
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('slotsForElementProvider')]
    public function testSlotForElement(
        string $element_name,
        array $all_identifiers,
        ?Identifier $matching_identifier
    ): void {
        $helper = new ElementHelper(
            $this->getMockHandler('to ' . $element_name, $all_identifiers),
            $this->getMockPathFactory(),
            $this->getMockNavigatorFactory(),
            $this->getMockConditionChecker($element_name, $matching_identifier)
        );
        $element = self::getMockElement($element_name);

        $actual_identifier = $helper->slotForElement($element);

        $this->assertSame($matching_identifier ?? Identifier::NULL, $actual_identifier);
    }

    public static function potentialSlotForElementByConditionProvider(): array
    {
        return [
            [
                'some element',
                'element in condition',
                'condition value',
                [],
                [],
                [],
                null
            ],
            [
                'some element',
                'element in condition',
                'condition value',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS],
                [],
                [],
                null
            ],
            [
                'some element',
                'element in condition',
                'condition value',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS],
                [Identifier::GENERAL_STRUCTURE->value => 'from some element to element in condition'],
                [Identifier::GENERAL_STRUCTURE->value => 'condition value'],
                Identifier::GENERAL_STRUCTURE
            ],
            [
                'some element',
                'element in condition',
                'wrong value',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS],
                [Identifier::GENERAL_STRUCTURE->value => 'from some element to element in condition'],
                [Identifier::GENERAL_STRUCTURE->value => 'condition value'],
                null
            ],
            [
                'some element',
                'wrong element in condition',
                'condition value',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS],
                [Identifier::GENERAL_STRUCTURE->value => 'from some element to element in condition'],
                [Identifier::GENERAL_STRUCTURE->value => 'condition value'],
                null
            ],
            [
                'some element',
                'element in condition',
                'condition value',
                [Identifier::EDUCATIONAL_CONTEXT, Identifier::GENERAL_STRUCTURE, Identifier::LIFECYCLE_STATUS],
                [Identifier::GENERAL_STRUCTURE->value => 'from some element to element in condition', Identifier::LIFECYCLE_STATUS->value => 'different path'],
                [Identifier::GENERAL_STRUCTURE->value => 'condition value', Identifier::LIFECYCLE_STATUS->value => 'different value'],
                Identifier::GENERAL_STRUCTURE
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('potentialSlotForElementByConditionProvider')]
    public function testPotentialSlotForElementByCondition(
        string $element_name,
        string $element_in_condition_name,
        string $condition_value,
        array $all_identifiers,
        array $condition_paths_by_identifer,
        array $condition_values_by_identifer,
        ?Identifier $matching_identifier
    ): void {
        $helper = new ElementHelper(
            $this->getMockHandler('to ' . $element_name, $all_identifiers, $condition_paths_by_identifer, $condition_values_by_identifer),
            $this->getMockPathFactory(),
            $this->getMockNavigatorFactory(),
            $this->getMockConditionChecker()
        );
        $element = self::getMockElement($element_name);
        $element_in_condition = self::getMockElement($element_in_condition_name);

        $actual_identifier = $helper->potentialSlotForElementByCondition(
            $element,
            $element_in_condition,
            $condition_value
        );

        $this->assertSame($matching_identifier ?? Identifier::NULL, $actual_identifier);
    }

    public static function findElementOfConditionProvider(): array
    {
        $el1 = self::getMockElement('el1');
        $el2 = self::getMockElement('el2');
        $el3 = self::getMockElement('el3');
        $other_element = self::getMockElement('other_element');
        return [
            [
                'some element',
                [$el1, $el2, $el3],
                Identifier::EDUCATIONAL_CONTEXT,
                false,
                $el1,
                null
            ],
            [
                'some element',
                [$el1, $el2, $el3],
                Identifier::EDUCATIONAL_CONTEXT,
                true,
                null,
                null
            ],
            [
                'some element',
                [$el1, $el2, $el3],
                Identifier::EDUCATIONAL_CONTEXT,
                true,
                $other_element,
                null
            ],
            [
                'some element',
                [$el1, $el2, $el3],
                Identifier::EDUCATIONAL_CONTEXT,
                true,
                $el2,
                'el2'
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('findElementOfConditionProvider')]
    public function testFindElementOfCondition(
        string $element_name,
        array $all_elements,
        Identifier $identifier,
        bool $conditional,
        ?ElementInterface $navigator_returns_element,
        ?string $expected_element_name
    ): void {
        $condition_paths_by_identifer = [];
        $condition_values_by_identifer = [];
        if ($conditional) {
            $condition_paths_by_identifer[$identifier->value] = 'some path';
            $condition_values_by_identifer[$identifier->value] = 'some value';
        }
        $helper = new ElementHelper(
            $this->getMockHandler('', [$identifier], $condition_paths_by_identifer, $condition_values_by_identifer),
            $this->getMockPathFactory(),
            $this->getMockNavigatorFactory('some path', $element_name, $navigator_returns_element),
            $this->getMockConditionChecker()
        );

        $found = $helper->findElementOfCondition(
            $identifier,
            self::getMockElement($element_name),
            ...$all_elements
        );

        $this->assertSame($expected_element_name, $found?->getDefinition()?->name());
    }
}
