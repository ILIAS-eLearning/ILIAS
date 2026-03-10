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

namespace ILIAS\Questions\Setup;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableDefinitions;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Question\Persistence\TableDefinitions as QuestionTableDefinitions;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Agent as SetupAgent;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Config;

class Agent implements SetupAgent
{
    use HasNoNamedObjective;

    /**
     * @param array<\ILIAS\Questions\AnswerForm\Migration\Migration> $answer_form_migrations
     * @param array<\ILIAS\Questions\AnswerForm\Capabilities\Migration> $capability_migrations
     */
    public function __construct(
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableNameBuilder $question_table_name_builder,
        private readonly array $answer_form_migrations,
        private readonly array $capability_migrations
    ) {
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new LogicException(self::class . ' has no Config.');
    }

    #[\Override]
    public function getUpdateObjective(
        ?Config $config = null
    ): Objective {
        return new ObjectiveCollection(
            'Database is updated for ILIAS\Questions',
            false,
            new \ilDatabaseUpdateStepsExecutedObjective(
                new OverarchingQuestionTables(
                    $this->question_table_name_builder
                )
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new ClozeQuestionTables(
                    new SetupTableNameBuilder(
                        new TableSubNameSpace(
                            'ILIAS',
                            'cloze'
                        )
                    ),
                    new TableDefinitions(
                        $this->persistence_factory,
                        new TableSubNameSpace(
                            'ILIAS',
                            'cloze'
                        )
                    )
                )
            ),
            new \ilTreeAdminNodeAddedObjective(
                'qsts',
                'Questions'
            )
        );
    }

    #[\Override]
    public function getStatusObjective(
        Storage $storage
    ): Objective {
        return new ObjectiveCollection(
            'ILIAS\Questions',
            true,
            new \ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new OverarchingQuestionTables(
                    $this->question_table_name_builder
                )
            ),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new ClozeQuestionTables(
                    new SetupTableNameBuilder(
                        new TableNameSpaceCore('cloze')
                    )
                )
            )
        );
    }

    #[\Override]
    public function getMigrations(): array
    {
        return [
            new QuestionsMigration(
                $this->persistence_factory,
                $this->question_table_name_builder,
                new QuestionTableDefinitions(
                    $this->persistence_factory
                ),
                new AnswerFormGenericTableDefinitions(
                    $this->persistence_factory
                ),
                $this->answer_form_migrations,
                $this->capability_migrations
            )
        ];
    }

    #[\Override]
    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    #[\Override]
    public function getInstallObjective(
        ?Config $config = null
    ): Objective {
        return new NullObjective();
    }


    #[\Override]
    public function getNamedObjectives(
        ?Config $config = null
    ): array {
        return new NullObjective();
    }
}
