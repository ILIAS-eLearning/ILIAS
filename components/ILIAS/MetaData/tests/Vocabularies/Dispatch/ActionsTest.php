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
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\InfosInterface;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Controlled\NullRepository as NullControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\NullInfos;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Standard\NullRepository as NullStandardRepo;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\ConditionInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\NullCondition;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class ActionsTest extends TestCase
{
    public function getVocabulary(
        Type $type,
        string $id,
        SlotIdentifier $slot = SlotIdentifier::NULL
    ): VocabularyInterface {
        return new class ($type, $id, $slot) extends NullVocabulary {
            public function __construct(
                protected Type $type,
                protected string $id,
                protected SlotIdentifier $slot
            ) {
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function id(): string
            {
                return $this->id;
            }

            public function slot(): SlotIdentifier
            {
                return $this->slot;
            }
        };
    }

    public function getInfos(
        bool $is_deactivatable = true,
        bool $can_disallow_custom_input = true,
        bool $is_custom_input_applicable = true,
        bool $can_be_deleted = true
    ): InfosInterface {
        return new class (
            $is_deactivatable,
            $can_disallow_custom_input,
            $is_custom_input_applicable,
            $can_be_deleted
        ) extends NullInfos {
            public function __construct(
                protected bool $is_deactivatable,
                protected bool $can_disallow_custom_input,
                protected bool $is_custom_input_applicable,
                protected bool $can_be_deleted
            ) {
            }

            public function isDeactivatable(VocabularyInterface $vocabulary): bool
            {
                return $this->is_deactivatable;
            }

            public function canDisallowCustomInput(VocabularyInterface $vocabulary): bool
            {
                return $this->can_disallow_custom_input;
            }

            public function isCustomInputApplicable(VocabularyInterface $vocabulary): bool
            {
                return $this->is_custom_input_applicable;
            }

            public function canBeDeleted(VocabularyInterface $vocabulary): bool
            {
                return $this->can_be_deleted;
            }
        };
    }

    public function getControlledRepo(): ControlledRepo
    {
        return new class () extends NullControlledRepo {
            public array $changes_to_active = [];
            public array $changes_to_custom_input = [];
            public array $exposed_deletions = [];

            public function setActiveForVocabulary(
                string $vocab_id,
                bool $active
            ): void {
                $this->changes_to_active[] = ['id' => $vocab_id, 'active' => $active];
            }

            public function setCustomInputsAllowedForVocabulary(
                string $vocab_id,
                bool $custom_inputs
            ): void {
                $this->changes_to_custom_input[] = ['id' => $vocab_id, 'custom_inputs' => $custom_inputs];
            }

            public function deleteVocabulary(string $vocab_id): void
            {
                $this->exposed_deletions[] = $vocab_id;
            }
        };
    }

    public function getStandardRepo(): StandardRepo
    {
        return new class () extends NullStandardRepo {
            public array $changes_to_active = [];

            public function activateVocabulary(SlotIdentifier $slot): void
            {
                $this->changes_to_active[] = [
                    'slot' => $slot,
                    'active' => true
                ];
            }

            public function deactivateVocabulary(SlotIdentifier $slot): void
            {
                $this->changes_to_active[] = [
                    'slot' => $slot,
                    'active' => false
                ];
            }
        };
    }

    public function testActivateStandard(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->activate($vocab);

        $this->assertSame(
            [['slot' => SlotIdentifier::LIFECYCLE_STATUS, 'active' => true]],
            $standard_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testActivateControlledString(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->activate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => true]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testActivateControlledVocabValue(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->activate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => true]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testActivateCopyright(): void
    {
        $actions = new Actions(
            $this->getInfos(false, false, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->activate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateNotDeactivatableException(): void
    {
        $actions = new Actions(
            $this->getInfos(false, false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $actions->deactivate($vocab);
    }

    public function testDeactivateStandard(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->deactivate($vocab);

        $this->assertSame(
            [['slot' => SlotIdentifier::LIFECYCLE_STATUS, 'active' => false]],
            $standard_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateControlledString(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->deactivate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => false]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateControlledVocabValue(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->deactivate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'active' => false]],
            $controlled_repo->changes_to_active
        );
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeactivateCopyright(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->deactivate($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testAllowCustomInputNotApplicableException(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $actions->allowCustomInput($vocab);
    }

    public function testAllowCustomInputStandard(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testAllowCustomInputControlledString(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'custom_inputs' => true]],
            $controlled_repo->changes_to_custom_input
        );
    }

    public function testAllowCustomInputControlledVocabValue(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testAllowCustomInputCopyright(): void
    {
        $actions = new Actions(
            $this->getInfos(false, false, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->allowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDisallowCustomInputNotApplicableException(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, false, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $actions->disallowCustomInput($vocab);
    }

    public function testDisallowCustomInputCannotDisallowException(): void
    {
        $actions = new Actions(
            $this->getInfos(true, false, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $this->expectException(\ilMDVocabulariesException::class);
        $actions->disallowCustomInput($vocab);
    }

    public function testDisallowCustomInputStandard(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::STANDARD, '', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDisallowCustomInputControlledString(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_STRING, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertSame(
            [['id' => 'vocab id', 'custom_inputs' => false]],
            $controlled_repo->changes_to_custom_input
        );
    }

    public function testDisallowCustomInputControlledVocabValue(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDisallowCustomInputCopyright(): void
    {
        $actions = new Actions(
            $this->getInfos(false, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );
        $vocab = $this->getVocabulary(Type::COPYRIGHT, 'vocab id', SlotIdentifier::LIFECYCLE_STATUS);

        $actions->disallowCustomInput($vocab);

        $this->assertEmpty($standard_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_active);
        $this->assertEmpty($controlled_repo->changes_to_custom_input);
    }

    public function testDeleteCannotBeDeletedException(): void
    {
        $actions = new Actions(
            $this->getInfos(false, true, true, false),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );

        $this->expectException(\ilMDVocabulariesException::class);
        $actions->delete($this->getVocabulary(Type::STANDARD, 'some id'));
    }

    public function testDeleteStandard(): void
    {
        $actions = new Actions(
            $this->getInfos(false, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );

        $actions->delete($this->getVocabulary(Type::STANDARD, 'some id'));

        $this->assertEmpty($controlled_repo->exposed_deletions);
    }

    public function testDeleteControlledString(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );

        $actions->delete($this->getVocabulary(Type::CONTROLLED_STRING, 'some id'));

        $this->assertSame(
            ['some id'],
            $controlled_repo->exposed_deletions
        );
    }

    public function testDeleteControlledVocabValue(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );

        $actions->delete($this->getVocabulary(Type::CONTROLLED_VOCAB_VALUE, 'some id'));

        $this->assertSame(
            ['some id'],
            $controlled_repo->exposed_deletions
        );
    }

    public function testDeleteCopyright(): void
    {
        $actions = new Actions(
            $this->getInfos(true, true, true, true),
            $controlled_repo = $this->getControlledRepo(),
            $standard_repo = $this->getStandardRepo(),
        );

        $actions->delete($this->getVocabulary(Type::COPYRIGHT, 'some id'));

        $this->assertEmpty($controlled_repo->exposed_deletions);
    }
}
