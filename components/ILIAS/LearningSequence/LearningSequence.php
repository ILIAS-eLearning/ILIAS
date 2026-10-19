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

class LearningSequence implements Component\Component
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
        $define[] = \ILIAS\LearningSequence\Content\Condition\ConditionTypes::class;

        $implement[\ILIAS\LearningSequence\Content\Condition\ConditionTypes::class] = static fn() =>
            $internal[\ILIAS\LearningSequence\Content\Condition\ConditionTypeRegistry::class];

        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilLearningSequenceSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class],
                $internal[\ILIAS\LearningSequence\Content\Condition\ConditionTypeRegistry::class]
            );

        $contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
                \ILIAS\LearningSequence\Content\Condition\InputCondition\PointsInputCondition\PointsInputCondition::class
            );
        $contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
                \ILIAS\LearningSequence\Content\Condition\InputCondition\SubsetInputCondition\SubsetInputCondition::class
            );
        $contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
                \ILIAS\LearningSequence\Content\Condition\InputCondition\LogicGateInputAwareCondition::class
            );
        $contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
                \ILIAS\LearningSequence\Content\Condition\InputCondition\LearningProgressInputConditions\LearningProgressInputAwareCondition::class
            );
        $contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
                \ILIAS\LearningSequence\Content\Condition\OutputCondition\AlwaysOutputCondition\AlwaysOutputCondition::class
            );
        $contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
                \ILIAS\LearningSequence\Content\Condition\OutputCondition\PointsOutputCondition\PointsOutputCondition::class
            );
        $contribute[\ILIAS\LearningSequence\Content\Condition\ConditionType::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition(
                \ILIAS\LearningSequence\Content\Condition\OutputCondition\LearningProgressOutputConditions\LearningProgressOutputAwareCondition::class
            );

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/lso_learning_map.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentCSS($this, "css/lso_learning_map.css");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentCSS($this, "css/lso_player.css");

        $internal[\ILIAS\LearningSequence\Content\Condition\ConditionTypeRegistry::class] = static fn() =>
            new \ILIAS\LearningSequence\Content\Condition\ConditionTypeRegistry(
                $seek[\ILIAS\LearningSequence\Content\Condition\ConditionType::class]
            );
    }
}
