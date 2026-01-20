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

use ILIAS\Questions\AnswerForm\Definition as AnswerFormDefinition;
use ILIAS\Questions\AnswerForm\Migration\Migration as AnswerFormMigration;
use ILIAS\Questions\AnswerFormTypes\Cloze\Migration\MigrationCloze;
use ILIAS\Questions\AnswerFormTypes\Cloze\Migration\MigrationLongMenu;
use ILIAS\Questions\AnswerFormTypes\Cloze\Migration\MigrationNumeric;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\Persistence\TableNameSpaceCore;
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
        $define[] = AnswerFormDefinition::class;
        $contribute[AgentInterface::class] = static fn() =>
            new Agent(
                $seek[AnswerFormMigration::class]
            );
        $contribute[AnswerFormMigration::class] = static fn() => new MigrationCloze(
            $internal[Persistence::class],
            $internal[\EvalMath::class]
        );
        $contribute[AnswerFormMigration::class] = static fn() => new MigrationLongMenu(
            $internal[Persistence::class]
        );
        $contribute[AnswerFormMigration::class] = static fn() => new MigrationNumeric(
            $internal[Persistence::class],
            $internal[\EvalMath::class]
        );
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, 'js/dist/ParticipantViewLongMenu.js');

        $internal[Persistence::class] = static fn() => new Persistence(
            new TableNameSpaceCore('cloze')
        );
        $internal[\EvalMath::class] = static fn() => new \EvalMath();
    }
}
