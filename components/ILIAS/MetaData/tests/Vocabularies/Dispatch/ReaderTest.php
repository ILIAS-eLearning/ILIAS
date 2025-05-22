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

namespace ILIAS\MetaData\Vocabularies\Dispatch;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\InfosInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\NullInfos;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository as NullControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;
use ILIAS\MetaData\Vocabularies\Standard\NullRepository as NullStandardRepo;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;
use ILIAS\MetaData\Vocabularies\Copyright\NullBridge as NullCopyrightBridge;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;
use ILIAS\MetaData\Vocabularies\Type;

class ReaderTest extends TestCase
{
    protected function getVocabulary(
        bool $active,
        string $id = '',
        Type $type = Type::NULL
    ): VocabularyInterface {
        return new class ($active, $type, $id) extends NullVocabulary {
            public function __construct(
                protected bool $active,
                protected Type $type,
                protected string $id
            ) {
            }

            public function isActive(): bool
            {
                return $this->active;
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function id(): string
            {
                return $this->id;
            }
        };
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabsWithIDs(bool $active, string ...$ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $result[] = $this->getVocabulary($active, $id);
        }
        return $result;
    }

    protected function getCopyrightBridge(?VocabularyInterface $vocabulary = null): CopyrightBridge
    {
        return new class ($vocabulary) extends NullCopyrightBridge {
            public function __construct(protected ?VocabularyInterface $vocabulary)
            {
            }

            public function vocabulary(SlotIdentifier $slot): ?VocabularyInterface
            {
                return $this->vocabulary;
            }
        };
    }

    protected function getControlledRepo(VocabularyInterface ...$vocabularies): ControlledRepo
    {
        return new class ($vocabularies) extends NullControlledRepo {
            public function __construct(protected array $vocabularies)
            {
            }

            public function getVocabulary(string $vocab_id): VocabularyInterface
            {
                foreach ($this->vocabularies as $vocabulary) {
                    if ($vocab_id === $vocabulary->id()) {
                        return $vocabulary;
                    }
                }
                return new NullVocabulary();
            }

            public function getVocabulariesForSlots(SlotIdentifier ...$slots): \Generator
            {
                yield from $this->vocabularies;
            }

            public function getActiveVocabulariesForSlots(SlotIdentifier ...$slots): \Generator
            {
                foreach ($this->vocabularies as $vocabulary) {
                    if (!$vocabulary->isActive()) {
                        continue;
                    }
                    yield $vocabulary;
                }
            }
        };
    }

    protected function getStandardRepo(VocabularyInterface ...$vocabularies): StandardRepo
    {
        return new class ($vocabularies) extends NullStandardRepo {
            public function __construct(protected array $vocabularies)
            {
            }

            public function getVocabulary(SlotIdentifier $slot): VocabularyInterface
            {
                foreach ($this->vocabularies as $vocabulary) {
                    if ($slot->value === $vocabulary->id()) {
                        return $vocabulary;
                    }
                }
                return new NullVocabulary();
            }

            public function getVocabularies(SlotIdentifier ...$slots): \Generator
            {
                yield from $this->vocabularies;
            }

            public function getActiveVocabularies(SlotIdentifier ...$slots): \Generator
            {
                foreach ($this->vocabularies as $vocabulary) {
                    if (!$vocabulary->isActive()) {
                        continue;
                    }
                    yield $vocabulary;
                }
            }
        };
    }

    /**
     * @param string[] $ids
     * @param VocabularyInterface[] $vocabs
     */
    protected function assertVocabIDsMatch(array $ids, \Generator $vocabs): void
    {
        $actual_ids = [];
        foreach ($vocabs as $vocabulary) {
            $actual_ids[] = $vocabulary->id();
        }
        $this->assertSame($ids, $actual_ids);
    }

    public function testVocabularyInvalidVocabID(): void
    {
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, SlotIdentifier::GENERAL_COVERAGE->value);
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, SlotIdentifier::RIGHTS_COST->value);

        $reader = new Reader(
            $this->getCopyrightBridge(),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $vocab = $reader->vocabulary('something entirely different');

        $this->assertInstanceOf(NullVocabulary::class, $vocab);
    }

    public function testVocabularyInvalidVocabIDFromStandardSlot(): void
    {
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, SlotIdentifier::GENERAL_COVERAGE->value);
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, SlotIdentifier::RIGHTS_COST->value);

        $reader = new Reader(
            $this->getCopyrightBridge(),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $vocab = $reader->vocabulary(SlotIdentifier::RIGHTS_COST->value);

        $this->assertSame(SlotIdentifier::RIGHTS_COST->value, $vocab->id());
    }
    public function testVocabularyInvalidVocabIDFromCopyrightSlot(): void
    {
        $cp_vocab = $this->getVocabulary(true, SlotIdentifier::RIGHTS_DESCRIPTION->value);
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, SlotIdentifier::GENERAL_COVERAGE->value);
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, SlotIdentifier::RIGHTS_COST->value);

        $reader = new Reader(
            $this->getCopyrightBridge($cp_vocab),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $vocab = $reader->vocabulary(SlotIdentifier::RIGHTS_DESCRIPTION->value);

        $this->assertSame(SlotIdentifier::RIGHTS_DESCRIPTION->value, $vocab->id());
    }

    public function testVocabularyInvalidVocabIDFromControlledVocabulary(): void
    {
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, SlotIdentifier::GENERAL_COVERAGE->value);
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, SlotIdentifier::RIGHTS_COST->value);

        $reader = new Reader(
            $this->getCopyrightBridge(),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $vocab = $reader->vocabulary('contr active 1');

        $this->assertSame('contr active 1', $vocab->id());
    }

    public function testVocabulariesForSlots(): void
    {
        $cp_vocab = $this->getVocabulary(true, 'cp');
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $reader = new Reader(
            $this->getCopyrightBridge($cp_vocab),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'cp',
                'contr active 1',
                'contr active 2',
                'contr inactive 1',
                'contr inactive 2',
                'stand active 1',
                'stand active 2',
                'stand inactive 1',
                'stand inactive 2'
            ],
            $reader->vocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testVocabulariesForSlotsNoCopyright(): void
    {
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $reader = new Reader(
            $this->getCopyrightBridge(),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'contr active 1',
                'contr active 2',
                'contr inactive 1',
                'contr inactive 2',
                'stand active 1',
                'stand active 2',
                'stand inactive 1',
                'stand inactive 2'
            ],
            $reader->vocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testActiveVocabulariesForSlots(): void
    {
        $cp_vocab = $this->getVocabulary(true, 'cp');
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $reader = new Reader(
            $this->getCopyrightBridge($cp_vocab),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'cp',
                'contr active 1',
                'contr active 2',
                'stand active 1',
                'stand active 2'
            ],
            $reader->activeVocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testActiveVocabulariesForSlotsNoCopyright(): void
    {
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $reader = new Reader(
            $this->getCopyrightBridge(),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'contr active 1',
                'contr active 2',
                'stand active 1',
                'stand active 2'
            ],
            $reader->activeVocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }

    public function testActiveVocabulariesForSlotsCopyrightVocabInactive(): void
    {
        $cp_vocab = $this->getVocabulary(false, 'cp');
        $active_controlled_vocabs = $this->getVocabsWithIDs(true, 'contr active 1', 'contr active 2');
        $inactive_controlled_vocabs = $this->getVocabsWithIDs(false, 'contr inactive 1', 'contr inactive 2');
        $active_standard_vocabs = $this->getVocabsWithIDs(true, 'stand active 1', 'stand active 2');
        $inactive_standard_vocabs = $this->getVocabsWithIDs(false, 'stand inactive 1', 'stand inactive 2');

        $reader = new Reader(
            $this->getCopyrightBridge($cp_vocab),
            $this->getControlledRepo(...$active_controlled_vocabs, ...$inactive_controlled_vocabs),
            $this->getStandardRepo(...$active_standard_vocabs, ...$inactive_standard_vocabs)
        );

        $this->assertVocabIDsMatch(
            [
                'contr active 1',
                'contr active 2',
                'stand active 1',
                'stand active 2'
            ],
            $reader->activeVocabulariesForSlots(SlotIdentifier::RIGHTS_COST)
        );
    }
}
