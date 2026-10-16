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

namespace ILIAS\Questions\Legacy;

use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Capabilities;
use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableDefinitions;
use ILIAS\Questions\AnswerFormTypes\Cloze;
use ILIAS\Questions\Attempt\Repository as AttemptRepository;
use ILIAS\Questions\Attempt\TableDefinitions as AttemptTableDefinitions;
use ILIAS\Questions\DefaultPublicInterface;
use ILIAS\Questions\Question\Persistence\DatabaseStatementBuilder;
use ILIAS\Questions\Question\Persistence\TableDefinitions as QuestionTableDefinitions;
use ILIAS\Questions\Persistence\TableSubNameSpace;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Question\Persistence\Repository as QuestionsRepository;
use ILIAS\Questions\Presentation\Layout\Factory as LayoutFactory;
use ILIAS\Questions\PublicInterface;
use ILIAS\Questions\UserSettings\CreateMode;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\DI\Container as ILIASContainer;
use Mustache\Engine as MustacheEngine;
use Pimple\Container as PimpleContainer;

class LocalDIC extends PimpleContainer
{
    protected static ?self $dic = null;

    public static function dic(): self
    {
        if (!self::$dic) {
            global $DIC;
            self::$dic = self::buildDIC($DIC);
        }
        return self::$dic;
    }

    protected static function buildDIC(ILIASContainer $DIC): self
    {
        $dic = new self();
        $dic[DataFactory::class] = static fn($c): DataFactory => new DataFactory();
        $dic[UuidFactory::class] = static fn($c): UuidFactory => new UuidFactory();
        $dic[MustacheEngine::class] = static fn($c): MustacheEngine
                => new MustacheEngine(['escape' => static fn($v) => $v]);
        $dic[\ilFileServicesFilenameSanitizer::class] = static fn($c): \ilFileServicesFilenameSanitizer
            => new \ilFileServicesFilenameSanitizer(
                $DIC->fileServiceSettings()
            );

        $dic[PublicInterface::class] = static fn($c): PublicInterface
            => new DefaultPublicInterface(
                $DIC['lng'],
                $DIC['user']->getLoggedInUser(),
                $DIC['refinery'],
                $DIC['ui.factory'],
                $DIC['ui.renderer'],
                $DIC['global_screen'],
                $DIC['tpl'],
                $DIC->contentStyle(),
                $DIC['ilCtrl'],
                $DIC['http'],
                $DIC['ilTabs'],
                $DIC->uiService(),
                $c[UuidFactory::class],
                $c[ConfigurationRepository::class],
                $c[AnswerFormFactory::class],
                $c[QuestionsRepository::class],
                $c[AttemptRepository::class],
                $c[LayoutFactory::class],
                $c[Capabilities\Factory::class]
            );

        $dic[ConfigurationRepository::class] = static fn($c): ConfigurationRepository
            => new ConfigurationRepository(
                $DIC['ilSetting'],
                $DIC['user']->getSettings()->getSettingByDefinitionClass(
                    CreateMode::class
                ),
                new \ilSetting('questions')
            );
        $dic[PersistenceFactory::class] = static fn($c): PersistenceFactory
            => new PersistenceFactory();
        $dic[QuestionTableDefinitions::class] = static fn($c): QuestionTableDefinitions
            => new QuestionTableDefinitions(
                $c[PersistenceFactory::class]
            );
        $dic[AnswerFormGenericTableDefinitions::class] = static fn($c): AnswerFormGenericTableDefinitions
            => new AnswerFormGenericTableDefinitions(
                $c[PersistenceFactory::class]
            );
        $dic[DatabaseStatementBuilder::class] = static fn($c): DatabaseStatementBuilder
            => new DatabaseStatementBuilder(
                $c[PersistenceFactory::class],
                $c[QuestionTableDefinitions::class],
                $c[AnswerFormGenericTableDefinitions::class]
            );
        $dic[AttemptTableDefinitions::class] = static fn($c): AttemptTableDefinitions
            => new AttemptTableDefinitions(
                $c[PersistenceFactory::class]
            );
        $dic[AttemptRepository::class] = static fn($c): AttemptRepository
            => new AttemptRepository(
                $DIC['ilDB'],
                $DIC['refinery'],
                $c[UuidFactory::class],
                $c[PersistenceFactory::class],
                $c[AttemptTableDefinitions::class]
            );
        $dic[Capabilities\Factory::class] = static fn($c): Capabilities\Factory
            => new Capabilities\Factory([
                new Capabilities\TextFeedback\Capability(
                    $c[DataFactory::class]->text(),
                    new Capabilities\TextFeedback\Repository(
                        $DIC['ilDB'],
                        $DIC['refinery'],
                        $c[UuidFactory::class],
                        $c[DataFactory::class]->text(),
                        $c[PersistenceFactory::class],
                        new Capabilities\TextFeedback\TableDefinitions(
                            $c[PersistenceFactory::class]
                        )
                    )
                ),
                new Capabilities\SuggestedLearningContent\Capability(
                    $DIC['ilCtrl'],
                    $DIC['rbacsystem'],
                    $DIC['tree'],
                    $DIC['static_url'],
                    $DIC['user']->getLoggedInUser(),
                    new Capabilities\SuggestedLearningContent\Repository(
                        $DIC['ilDB'],
                        $DIC['refinery'],
                        $DIC['resource_storage'],
                        $c[PersistenceFactory::class],
                        new Capabilities\SuggestedLearningContent\TableDefinitions(
                            $c[PersistenceFactory::class]
                        )
                    )
                ),
                new Capabilities\MarkingAllowingPartialPoints\Capability(),
                new Capabilities\DefaultView\Capability(),
                new Capabilities\AsyncView\Capability()
            ]);
        $dic[AnswerFormFactory::class] = static fn($c): AnswerFormFactory
            => new AnswerFormFactory(
                $c[UuidFactory::class],
                [
                    $c[Cloze\Definition::class]
                ]
            );
        $dic[QuestionsRepository::class] = static fn($c): QuestionsRepository
            => new QuestionsRepository(
                $DIC['ilDB'],
                $DIC['refinery'],
                $c[UuidFactory::class],
                $c[DatabaseStatementBuilder::class],
                $c[PersistenceFactory::class],
                $c[QuestionTableDefinitions::class],
                $c[AnswerFormGenericTableDefinitions::class],
                $c[AnswerFormFactory::class]
            );
        $dic[LayoutFactory::class] = static fn($c): LayoutFactory =>
            new LayoutFactory(
                $DIC['ui.factory'],
                $DIC['http'],
                $DIC['lng'],
                $DIC['resource_storage'],
                $DIC['filesystem']->temp(),
                $DIC['upload'],
                $c[\ilFileServicesFilenameSanitizer::class]
            );
        $dic[Cloze\Properties\ClozeText\Factory::class] = static fn($c): Cloze\Properties\ClozeText\Factory
            => new Cloze\Properties\ClozeText\Factory(
                $DIC['refinery'],
                $c[MustacheEngine::class],
                $c[DataFactory::class]->text()
            );
        $dic[Cloze\Properties\Gaps\AnswerOptions\Factory::class] = static fn($c): Cloze\Properties\Gaps\AnswerOptions\Factory
            => new Cloze\Properties\Gaps\AnswerOptions\Factory(
                $c[UuidFactory::class],
                $DIC['refinery']
            );
        $dic[Cloze\Properties\Gaps\Factory::class] = static fn($c): Cloze\Properties\Gaps\Factory
            => new Cloze\Properties\Gaps\Factory(
                $DIC['refinery'],
                $c[UuidFactory::class],
                $c[Cloze\Properties\Gaps\AnswerOptions\Factory::class],
                [
                    new Cloze\Properties\Gaps\Text(
                        $DIC['lng'],
                        $DIC['refinery']
                    ),
                    new Cloze\Properties\Gaps\Numeric(
                        $DIC['lng'],
                        $DIC['refinery']
                    ),
                    new Cloze\Properties\Gaps\Select(
                        $DIC['lng'],
                        $DIC['refinery']
                    ),
                    new Cloze\Properties\Gaps\LongMenu(
                        $DIC['lng'],
                        $DIC['refinery'],
                        $DIC['tpl']
                    )
                ]
            );
        $dic[Cloze\TableDefinitions::class] = static fn($c): Cloze\TableDefinitions
            => new Cloze\TableDefinitions(
                $c[PersistenceFactory::class],
                new TableSubNameSpace(
                    'ILIAS',
                    'cloze'
                )
            );
        $dic[Cloze\Properties\Factory::class] = static fn($c): Cloze\Properties\Factory
            => new Cloze\Properties\Factory(
                $c[PersistenceFactory::class],
                $c[Cloze\Properties\ClozeText\Factory::class],
                $c[Cloze\Properties\Gaps\Factory::class],
                $c[Cloze\Properties\Combinations\Factory::class]
            );
        $dic[Cloze\Response\Factory::class] = static fn($c): Cloze\Response\Factory
            => new Cloze\Response\Factory(
                $c[UuidFactory::class],
                $c[PersistenceFactory::class]
            );
        $dic[Cloze\Properties\Gaps\Edit::class] = static fn($c): Cloze\Properties\Gaps\Edit
            => new Cloze\Properties\Gaps\Edit(
                $DIC['upload'],
                $c[Cloze\Properties\Factory::class],
                $c[Cloze\Properties\Gaps\Factory::class]
            );
        $dic[Cloze\Views\Edit::class] = static fn($c): Cloze\Views\Edit
            => new Cloze\Views\Edit(
                $c[Cloze\Properties\Factory::class],
                $c[Cloze\Properties\ClozeText\Factory::class],
                $c[Cloze\Properties\Gaps\Edit::class]
            );
        $dic[Cloze\Definition::class] = static fn($c): Cloze\Definition => new Cloze\Definition(
            $c[Cloze\TableDefinitions::class],
            [
                new Cloze\Capabilities\TextFeedback(
                    $c[UuidFactory::class],
                    $c[DataFactory::class]->text()
                ),
                new Cloze\Capabilities\MarkingAllowingPartialPoints(
                    $c[Cloze\Properties\Factory::class],
                    $c[Cloze\Response\Factory::class]
                ),
                new Cloze\Capabilities\DefaultView(
                    $c[MustacheEngine::class],
                    $c[Cloze\Response\Factory::class]
                ),
                new Cloze\Capabilities\AsyncView(
                    $DIC['ui.renderer'],
                    $c[MustacheEngine::class],
                    $c[Cloze\Response\Factory::class]
                )
            ],
            $c[Cloze\Properties\Factory::class],
            $c[Cloze\Response\Factory::class],
            $c[Cloze\Views\Edit::class]
        );
        $dic[Cloze\Properties\Combinations\Factory::class] = static fn($c): Cloze\Properties\Combinations\Factory
            => new Cloze\Properties\Combinations\Factory(
                $c[UuidFactory::class],
                $c[PersistenceFactory::class],
                $c[Cloze\TableDefinitions::class]
            );

        return $dic;
    }
}
