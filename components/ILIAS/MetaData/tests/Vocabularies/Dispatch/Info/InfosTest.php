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

namespace ILIAS\MetaData\Vocabularies\Dispatch\Info;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepository;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepository;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository as NullControlledRepository;
use ILIAS\MetaData\Vocabularies\Standard\NullRepository as NullStandardRepository;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;

class InfosTest extends TestCase
{
    protected function getVocabulary(
        Type $type,
        bool $active = true,
        SlotIdentifier $slot = SlotIdentifier::NULL
    ): VocabularyInterface {
        return new class ($type, $active, $slot) extends NullVocabulary {
            public function __construct(
                protected Type $type,
                protected bool $active,
                protected SlotIdentifier $slot
            ) {
            }

            public function slot(): SlotIdentifier
            {
                return $this->slot;
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function isActive(): bool
            {
                return $this->active;
            }
        };
    }

    protected function getControlledRepo(
        SlotIdentifier $slot = SlotIdentifier::NULL,
        int $active_count = 0
    ): ControlledRepository {
        return new class ($slot, $active_count) extends NullControlledRepository {
            public function __construct(
                protected SlotIdentifier $slot,
                protected int $active_count
            ) {
            }

            public function countActiveVocabulariesForSlot(SlotIdentifier $slot): int
            {
                if ($slot === $this->slot) {
                    return $this->active_count;
                }
                return 0;
            }
        };
    }

    protected function getStandardRepo(
        SlotIdentifier $slot = SlotIdentifier::NULL,
        bool $active = false
    ): StandardRepository {
        return new class ($slot, $active) extends NullStandardRepository {
            public function __construct(
                protected SlotIdentifier $slot,
                protected bool $active
            ) {
            }

            public function isVocabularyActive(SlotIdentifier $slot): bool
            {
                if ($slot === $this->slot) {
                    return $this->active;
                }
                return false;
            }
        };
    }

    public function activeCountProvider(): array
    {
        return [
            [5, true, true, true],
            [5, true, false, true],
            [1, false, true, false],
            [1, false, false, true],
            [0, true, true, false],
            [0, true, false, true],
            [0, false, false, false]
        ];
    }

    /**
     * @dataProvider activeCountProvider
     */
    public function testIsDeactivatableStandard(
        int $active_controlled_vocabs,
        bool $is_standard_vocab_active,
        bool $is_vocab_active,
        bool $are_other_vocabs_active
    ): void {
        $infos = new Infos(
            $this->getControlledRepo(SlotIdentifier::TECHNICAL_FORMAT, $active_controlled_vocabs),
            $this->getStandardRepo(SlotIdentifier::TECHNICAL_FORMAT, $is_standard_vocab_active)
        );
        $vocab = $this->getVocabulary(
            Type::STANDARD,
            $is_vocab_active,
            SlotIdentifier::TECHNICAL_FORMAT
        );

        $this->assertSame(
            $are_other_vocabs_active,
            $infos->isDeactivatable($vocab)
        );
    }

    public function testIsDeactivatableControlledString(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING);

        $this->assertTrue($infos->isDeactivatable($vocab));
    }

    /**
     * @dataProvider activeCountProvider
     */
    public function testIsDeactivatableControlledVocabValue(
        int $active_controlled_vocabs,
        bool $is_standard_vocab_active,
        bool $is_vocab_active,
        bool $are_other_vocabs_active
    ): void {
        $infos = new Infos(
            $this->getControlledRepo(SlotIdentifier::TECHNICAL_FORMAT, $active_controlled_vocabs),
            $this->getStandardRepo(SlotIdentifier::TECHNICAL_FORMAT, $is_standard_vocab_active)
        );
        $vocab = $this->getVocabulary(
            Type::CONTROLLED_VOCAB_VALUE,
            $is_vocab_active,
            SlotIdentifier::TECHNICAL_FORMAT
        );

        $this->assertSame(
            $are_other_vocabs_active,
            $infos->isDeactivatable($vocab)
        );
    }

    public function testIsDeactivatableCopyright(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::COPYRIGHT);

        $this->assertFalse($infos->isDeactivatable($vocab));
    }

    public function testCanDisallowCustomInputStandard(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::STANDARD);

        $this->assertFalse($infos->canDisallowCustomInput($vocab));
    }

    public function testCanDisallowCustomInputControlledString(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING);

        $this->assertTrue($infos->canDisallowCustomInput($vocab));
    }

    public function testCanDisallowCustomInputControlledVocabValue(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE);

        $this->assertFalse($infos->canDisallowCustomInput($vocab));
    }

    public function testCanDisallowCustomInputCopyright(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::COPYRIGHT);

        $this->assertFalse($infos->canDisallowCustomInput($vocab));
    }

    public function testIsCustomInputApplicableStandard(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::STANDARD);

        $this->assertFalse($infos->isCustomInputApplicable($vocab));
    }

    public function testIsCustomInputApplicableControlledString(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING);

        $this->assertTrue($infos->isCustomInputApplicable($vocab));
    }

    public function testIsCustomInputApplicableControlledVocabValue(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE);

        $this->assertFalse($infos->isCustomInputApplicable($vocab));
    }

    public function testIsCustomInputApplicableCopyright(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::COPYRIGHT);

        $this->assertTrue($infos->isCustomInputApplicable($vocab));
    }

    public function testCanBeDeletedStandard(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::STANDARD);

        $this->assertFalse($infos->canBeDeleted($vocab));
    }

    public function testCanBeDeletedControlledString(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING);

        $this->assertTrue($infos->canBeDeleted($vocab));
    }

    /**
     * @dataProvider activeCountProvider
     */
    public function testCanBeDeletedControlledVocabValue(
        int $active_controlled_vocabs,
        bool $is_standard_vocab_active,
        bool $is_vocab_active,
        bool $are_other_vocabs_active
    ): void {
        $infos = new Infos(
            $this->getControlledRepo(SlotIdentifier::TECHNICAL_FORMAT, $active_controlled_vocabs),
            $this->getStandardRepo(SlotIdentifier::TECHNICAL_FORMAT, $is_standard_vocab_active)
        );
        $vocab = $this->getVocabulary(
            Type::CONTROLLED_VOCAB_VALUE,
            $is_vocab_active,
            SlotIdentifier::TECHNICAL_FORMAT
        );

        $this->assertSame(
            $are_other_vocabs_active,
            $infos->canBeDeleted($vocab)
        );
    }

    public function testCanBeDeletedCopyright(): void
    {
        $infos = new Infos($this->getControlledRepo(), $this->getStandardRepo());
        $vocab = $this->getVocabulary(Type::COPYRIGHT);

        $this->assertFalse($infos->canBeDeleted($vocab));
    }
}
