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

namespace ILIAS;

use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\Migration as TextFeedbackMigration;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\TableDefinitions as TextFeedbackTableDefinitions;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Migration as CapabilityMigration;
use ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent\Migration as SuggestedLearningContentMigration;
use ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent\TableDefinitions as SuggestedLearningContentTableDefinitions;
use ILIAS\Questions\AnswerForm\Migration\Migration as AnswerFormMigration;
use ILIAS\Questions\AnswerFormTypes\Cloze\Migration\MigrationCloze;
use ILIAS\Questions\AnswerFormTypes\Cloze\Migration\MigrationLongMenu;
use ILIAS\Questions\AnswerFormTypes\Cloze\Migration\MigrationNumeric;
use ILIAS\Questions\AnswerFormTypes\Cloze\Migration\MigrationTextSubset;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions as ClozeTableDefinitions;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Setup\Agent;
use ILIAS\Setup\Agent as AgentInterface;

class Questions implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = Questions\PublicInterface::class;

        $internal[PersistenceFactory::class] = static fn()
            => new PersistenceFactory();
        $internal[\EvalMath::class] = static fn() => new \EvalMath();

        $contribute[AgentInterface::class] = static fn() =>
            new Agent(
                $internal[PersistenceFactory::class],
                new TableNameBuilder(
                    'qsts',
                    null
                ),
                $seek[AnswerFormMigration::class],
                $seek[CapabilityMigration::class]
            );
        $contribute[AnswerFormMigration::class] = static fn()
            => new MigrationCloze(
                new ClozeTableDefinitions(
                    $internal[PersistenceFactory::class],
                    new TableSubNameSpace(
                        'ILIAS',
                        'cloze'
                    )
                ),
                $internal[\EvalMath::class]
            );
        $contribute[AnswerFormMigration::class] = static fn()
            => new MigrationLongMenu(
                new ClozeTableDefinitions(
                    $internal[PersistenceFactory::class],
                    new TableSubNameSpace(
                        'ILIAS',
                        'cloze'
                    )
                )
            );
        $contribute[AnswerFormMigration::class] = static fn()
            => new MigrationNumeric(
                new ClozeTableDefinitions(
                    $internal[PersistenceFactory::class],
                    new TableSubNameSpace(
                        'ILIAS',
                        'cloze'
                    )
                ),
                $internal[\EvalMath::class]
            );
        $contribute[AnswerFormMigration::class] = static fn()
            => new MigrationTextSubset(
                new ClozeTableDefinitions(
                    $internal[PersistenceFactory::class],
                    new TableSubNameSpace(
                        'ILIAS',
                        'cloze'
                    )
                )
            );

        $contribute[CapabilityMigration::class] = static fn()
            => new TextFeedbackMigration(
                new TextFeedbackTableDefinitions(
                    $internal[PersistenceFactory::class]
                )
            );
        $contribute[CapabilityMigration::class] = static fn()
            => new SuggestedLearningContentMigration(
                new SuggestedLearningContentTableDefinitions(
                    $internal[PersistenceFactory::class]
                )
            );

        $contribute[Component\Resource\PublicAsset::class] = fn()
            => new Component\Resource\ComponentJS(
                $this,
                'js/dist/ParticipantViewLongMenu.js'
            );
        $contribute[User\Settings\UserSettings::class] = fn()
            => new Questions\UserSettings\Settings();
    }
}
