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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface;
use ILIAS\MetaData\Vocabularies\Factory\NullFactory;
use ILIAS\MetaData\Vocabularies\Factory\BuilderInterface;
use ILIAS\MetaData\Vocabularies\Factory\NullBuilder;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Standard\Assignment\AssignmentsInterface;
use ILIAS\MetaData\Vocabularies\Standard\Assignment\NullAssignments;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Presentation\NullUtilities;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;

class RepositoryTest extends TestCase
{
    protected function getGateway(SlotIdentifier ...$deactivation_entries): GatewayInterface
    {
        return new class ($deactivation_entries) extends NullGateway {
            public function __construct(
                public array $exposed_deactivation_entries
            ) {
            }

            public function createDeactivationEntry(SlotIdentifier $slot): void
            {
                $this->exposed_deactivation_entries[] = $slot;
            }

            public function deleteDeactivationEntry(SlotIdentifier $slot): void
            {
                if (!in_array($slot, $this->exposed_deactivation_entries)) {
                    return;
                }
                unset($this->exposed_deactivation_entries[array_search($slot, $this->exposed_deactivation_entries)]);
            }

            public function doesDeactivationEntryExistForSlot(SlotIdentifier $slot): bool
            {
                return in_array($slot, $this->exposed_deactivation_entries);
            }
        };
    }

    protected function getVocabFactory(): FactoryInterface
    {
        return new class () extends NullFactory {
            public function null(): VocabularyInterface
            {
                return new NullVocabulary();
            }

            public function standard(SlotIdentifier $slot, string ...$values): BuilderInterface
            {
                return new class ($slot, $values) extends NullBuilder {
                    protected bool $active = true;

                    public function __construct(
                        protected SlotIdentifier $slot,
                        protected array $values
                    ) {
                    }

                    public function withIsDeactivated(bool $deactivated): BuilderInterface
                    {
                        $clone = clone $this;
                        $clone->active = !$deactivated;
                        return $clone;
                    }

                    public function get(): VocabularyInterface
                    {
                        return new class ($this->slot, $this->active, $this->values) extends NullVocabulary {
                            public function __construct(
                                protected SlotIdentifier $slot,
                                protected bool $active,
                                protected array $values
                            ) {
                            }

                            public function slot(): SlotIdentifier
                            {
                                return $this->slot;
                            }

                            public function isActive(): bool
                            {
                                return $this->active;
                            }


                            public function values(): \Generator
                            {
                                yield from $this->values;
                            }
                        };
                    }
                };
            }
        };
    }

    protected function getAssignments(array $assignments = []): AssignmentsInterface
    {
        return new class ($assignments) extends NullAssignments {
            public function __construct(protected array $assignments)
            {
            }

            public function doesSlotHaveValues(SlotIdentifier $slot): bool
            {
                return array_key_exists($slot->value, $this->assignments);
            }

            public function valuesForSlot(SlotIdentifier $slot): \Generator
            {
                yield from $this->assignments[$slot->value] ?? [];
            }
        };
    }

    protected function getPresentationUtilities(): PresentationUtilities
    {
        return new class () extends NullUtilities {
            public function txt(string $key): string
            {
                return 'translated ' . $key;
            }
        };
    }

    protected function assertVocabularyMatches(
        VocabularyInterface $vocabulary,
        SlotIdentifier $expected_slot,
        bool $expected_active,
        string ...$expected_values
    ): void {
        $this->assertSame($expected_slot, $vocabulary->slot());
        $this->assertSame(
            $expected_active,
            $vocabulary->isActive(),
            'Vocab for slot ' . $expected_slot->value . ' activation should be ' . $expected_active
        );
        $this->assertSame(
            $expected_values,
            iterator_to_array($vocabulary->values()),
            'Vocab for slot ' . $expected_slot->value . ' values not correct.'
        );
    }

    protected function assertVocabulariesMatch(
        array $expected_content,
        array $deactivated_slots,
        VocabularyInterface ...$vocabularies,
    ): void {
        $this->assertCount(count($expected_content), $vocabularies);

        $i = 0;
        foreach ($expected_content as $expected_slot => $expected_values) {
            $slot = SlotIdentifier::from($expected_slot);
            $this->assertVocabularyMatches(
                $vocabularies[$i],
                $slot,
                !in_array($slot, $deactivated_slots),
                ...$expected_values
            );
            $i++;
        }
    }

    public function testDeactivateVocabularyInvalidSlot(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $repo->deactivateVocabulary(SlotIdentifier::NULL);

        $this->assertSame(
            $already_deactivated,
            $gateway->exposed_deactivation_entries
        );
    }

    public function testDeactivateVocabularyAlreadyDeactivated(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $repo->deactivateVocabulary(SlotIdentifier::EDUCATIONAL_DIFFICULTY);

        $this->assertSame(
            $already_deactivated,
            $gateway->exposed_deactivation_entries
        );
    }

    public function testDeactivateVocabulary(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $repo->deactivateVocabulary(SlotIdentifier::GENERAL_STRUCTURE);

        $this->assertSame(
            array_merge($already_deactivated, [SlotIdentifier::GENERAL_STRUCTURE]),
            $gateway->exposed_deactivation_entries
        );
    }

    public function testActivateVocabularyInvalidSlot(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $repo->activateVocabulary(SlotIdentifier::NULL);

        $this->assertSame(
            $already_deactivated,
            $gateway->exposed_deactivation_entries
        );
    }

    public function testActivateVocabulary(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $repo->activateVocabulary(SlotIdentifier::CLASSIFICATION_PURPOSE);

        $this->assertSame(
            [SlotIdentifier::EDUCATIONAL_DIFFICULTY],
            $gateway->exposed_deactivation_entries
        );
    }

    public function testActivateVocabularyAlreadyActive(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $repo->activateVocabulary(SlotIdentifier::GENERAL_STRUCTURE);

        $this->assertSame(
            $already_deactivated,
            $gateway->exposed_deactivation_entries
        );
    }

    public function testIsVocabularyActiveTrue(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $this->assertTrue($repo->isVocabularyActive(SlotIdentifier::GENERAL_STRUCTURE));
    }

    public function testIsVocabularyActiveFalse(): void
    {
        $already_deactivated = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        ];
        $repo = new Repository(
            $gateway = $this->getGateway(...$already_deactivated),
            $this->getVocabFactory(),
            $this->getAssignments()
        );

        $this->assertFalse($repo->isVocabularyActive(SlotIdentifier::EDUCATIONAL_DIFFICULTY));
    }

    public function testGetVocabularyInvalidSlot(): void
    {
        $repo = new Repository(
            $gateway = $this->getGateway(),
            $this->getVocabFactory(),
            $this->getAssignments([
                SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2'],
                SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4']
            ])
        );

        $vocab = $repo->getVocabulary(SlotIdentifier::LIFECYCLE_STATUS);
        $this->assertSame(SlotIdentifier::NULL, $vocab->slot());
        $this->assertEmpty(iterator_to_array($vocab->values()));
    }

    public function testGetVocabulary(): void
    {
        $repo = new Repository(
            $gateway = $this->getGateway(),
            $this->getVocabFactory(),
            $this->getAssignments([
                SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2'],
                SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4']
            ])
        );

        $vocab = $repo->getVocabulary(SlotIdentifier::EDUCATIONAL_DIFFICULTY);
        $this->assertSame(SlotIdentifier::EDUCATIONAL_DIFFICULTY, $vocab->slot());
        $this->assertSame(['value 1', 'value 2'], iterator_to_array($vocab->values()));
        $this->assertTrue($vocab->isActive());
    }

    public function testGetVocabularyInactive(): void
    {
        $repo = new Repository(
            $gateway = $this->getGateway(SlotIdentifier::EDUCATIONAL_DIFFICULTY),
            $this->getVocabFactory(),
            $this->getAssignments([
                SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2'],
                SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4']
            ])
        );

        $vocab = $repo->getVocabulary(SlotIdentifier::EDUCATIONAL_DIFFICULTY);
        $this->assertSame(SlotIdentifier::EDUCATIONAL_DIFFICULTY, $vocab->slot());
        $this->assertSame(['value 1', 'value 2'], iterator_to_array($vocab->values()));
        $this->assertFalse($vocab->isActive());
    }

    public function testGetVocabulariesEmpty(): void
    {
        $repo = new Repository(
            $gateway = $this->getGateway(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                SlotIdentifier::LIFECYCLE_STATUS
            ),
            $this->getVocabFactory(),
            $this->getAssignments([
                SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2'],
                SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4'],
                SlotIdentifier::LIFECYCLE_STATUS->value => ['value 5', 'value 6']
            ])
        );

        $vocabs = $repo->getVocabularies(SlotIdentifier::GENERAL_STRUCTURE);

        $this->assertNull($vocabs->current());
    }

    public function testGetVocabularies(): void
    {
        $expected_content = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2'],
            SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4'],
            SlotIdentifier::LIFECYCLE_STATUS->value => ['value 5', 'value 6']
        ];
        $deactivated_slots = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::LIFECYCLE_STATUS
        ];

        $repo = new Repository(
            $gateway = $this->getGateway(...$deactivated_slots),
            $this->getVocabFactory(),
            $this->getAssignments($expected_content)
        );

        $vocabs = $repo->getVocabularies(
            SlotIdentifier::GENERAL_STRUCTURE,
            SlotIdentifier::LIFECYCLE_STATUS,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        );

        $this->assertVocabulariesMatch(
            [
                SlotIdentifier::LIFECYCLE_STATUS->value => ['value 5', 'value 6'],
                SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4']
            ],
            $deactivated_slots,
            ...$vocabs
        );
    }

    public function testGetActiveVocabulariesEmpty(): void
    {
        $repo = new Repository(
            $gateway = $this->getGateway(
                SlotIdentifier::EDUCATIONAL_DIFFICULTY,
                SlotIdentifier::LIFECYCLE_STATUS
            ),
            $this->getVocabFactory(),
            $this->getAssignments([
                SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2'],
                SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4'],
                SlotIdentifier::LIFECYCLE_STATUS->value => ['value 5', 'value 6']
            ])
        );

        $vocabs = $repo->getActiveVocabularies(SlotIdentifier::GENERAL_STRUCTURE);

        $this->assertNull($vocabs->current());
    }

    public function testGetActiveVocabularies(): void
    {
        $expected_content = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2'],
            SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4'],
            SlotIdentifier::LIFECYCLE_STATUS->value => ['value 5', 'value 6'],
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER->value => ['value 7', 'value 8']
        ];
        $deactivated_slots = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            SlotIdentifier::LIFECYCLE_STATUS
        ];

        $repo = new Repository(
            $gateway = $this->getGateway(...$deactivated_slots),
            $this->getVocabFactory(),
            $this->getAssignments($expected_content)
        );

        $vocabs = $repo->getActiveVocabularies(
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER,
            SlotIdentifier::GENERAL_STRUCTURE,
            SlotIdentifier::LIFECYCLE_STATUS,
            SlotIdentifier::CLASSIFICATION_PURPOSE
        );

        $this->assertVocabulariesMatch(
            [
                SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER->value => ['value 7', 'value 8'],
                SlotIdentifier::CLASSIFICATION_PURPOSE->value => ['value 3', 'value 4']
            ],
            $deactivated_slots,
            ...$vocabs
        );
    }

    public function testGetLabelsForValues(): void
    {
        $vocab_values = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2', 'ProblemStatement', 'ispartof'],
        ];

        $repo = new Repository(
            $gateway = $this->getGateway(),
            $this->getVocabFactory(),
            $this->getAssignments($vocab_values)
        );

        $labelled_values = $repo->getLabelsForValues(
            $this->getPresentationUtilities(),
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            false,
            'ispartof',
            'value 2',
            'ProblemStatement',
            'something else'
        );

        $l1 = $labelled_values->current();
        $this->assertSame('ispartof', $l1->value());
        $this->assertSame('translated meta_is_part_of', $l1->label());
        $labelled_values->next();

        $l2 = $labelled_values->current();
        $this->assertSame('value 2', $l2->value());
        $this->assertSame('translated meta_value_2', $l2->label());
        $labelled_values->next();

        $l3 = $labelled_values->current();
        $this->assertSame('ProblemStatement', $l3->value());
        $this->assertSame('translated meta_problem_statement', $l3->label());
        $labelled_values->next();

        $this->assertNull($labelled_values->current());
    }

    public function testGetLabelsForValuesOnlyActiveActiveVocabulary(): void
    {
        $vocab_values = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2', 'ProblemStatement', 'ispartof'],
        ];

        $repo = new Repository(
            $gateway = $this->getGateway(),
            $this->getVocabFactory(),
            $this->getAssignments($vocab_values)
        );

        $labelled_values = $repo->getLabelsForValues(
            $this->getPresentationUtilities(),
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            true,
            'ispartof',
            'value 2',
            'ProblemStatement',
            'something else'
        );

        $l1 = $labelled_values->current();
        $this->assertSame('ispartof', $l1->value());
        $this->assertSame('translated meta_is_part_of', $l1->label());
        $labelled_values->next();

        $l2 = $labelled_values->current();
        $this->assertSame('value 2', $l2->value());
        $this->assertSame('translated meta_value_2', $l2->label());
        $labelled_values->next();

        $l3 = $labelled_values->current();
        $this->assertSame('ProblemStatement', $l3->value());
        $this->assertSame('translated meta_problem_statement', $l3->label());
        $labelled_values->next();

        $this->assertNull($labelled_values->current());
    }

    public function testGetLabelsForValuesOnlyActiveInactiveVocabulary(): void
    {
        $vocab_values = [
            SlotIdentifier::EDUCATIONAL_DIFFICULTY->value => ['value 1', 'value 2', 'ProblemStatement', 'ispartof'],
        ];

        $repo = new Repository(
            $gateway = $this->getGateway(SlotIdentifier::EDUCATIONAL_DIFFICULTY),
            $this->getVocabFactory(),
            $this->getAssignments($vocab_values)
        );

        $labelled_values = $repo->getLabelsForValues(
            $this->getPresentationUtilities(),
            SlotIdentifier::EDUCATIONAL_DIFFICULTY,
            true,
            'ispartof',
            'value 2',
            'ProblemStatement',
            'something else'
        );

        $this->assertNull($labelled_values->current());
    }
}
