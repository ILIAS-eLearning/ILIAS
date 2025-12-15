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

use ILIAS\Questions\Question\Persistence\Repository as QuestionsRepository;
use ILIAS\Questions\AnswerFormTypes\Cloze;
use ILIAS\Questions\Question\Persistence\TableNameSpaceCore;
use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Presentation\Layout\Definitions\Factory as DefinitionsFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\DI\Container as ILIASContainer;
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

        $dic[AnswerFormFactory::class] = static fn($c): AnswerFormFactory
            => new AnswerFormFactory(
                $c[UuidFactory::class],
                [
                    $c[Cloze\Definition::class]
                ]
            );
        $dic[QuestionsRepository::class] = static fn($c): QuestionsRepository =>
            new QuestionsRepository(
                $DIC['ilDB'],
                new UuidFactory(),
                $c[AnswerFormFactory::class]
            );
        $dic[DefinitionsFactory::class] = static fn($c): DefinitionsFactory =>
            new DefinitionsFactory(
                $DIC['ui.factory'],
                $DIC['lng']
            );
        $dic[Edit::class] = static fn($c): Edit => new Edit(
            $DIC['lng'],
            $DIC['ilUser'],
            $DIC['refinery'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['global_screen'],
            $DIC['ilCtrl'],
            $DIC['http'],
            $DIC->uiService(),
            $c[DataFactory::class],
            $c[UuidFactory::class],
            $c[AnswerFormFactory::class],
            $c[QuestionsRepository::class],
            $c[DefinitionsFactory::class]
        );

        $dic[Cloze\Properties\ClozeText\Factory::class] = static fn($c): Cloze\Properties\ClozeText\Factory
            => new Cloze\Properties\ClozeText\Factory(
                $DIC['refinery'],
                (new \ilMustacheFactory())->getBasicEngine(),
                $c[DataFactory::class]->text()
            );
        $dic[Cloze\Properties\Gaps\Properties\Factory::class] = static fn($c): Cloze\Properties\Gaps\Properties\Factory
            => new Cloze\Properties\Gaps\Properties\Factory(
                $c[UuidFactory::class],
                $DIC['refinery']
            );
        $dic[Cloze\Properties\Gaps\Factory::class] = static fn($c): Cloze\Properties\Gaps\Factory
            => new Cloze\Properties\Gaps\Factory(
                $c[UuidFactory::class],
                $c[Cloze\Properties\Gaps\Properties\Factory::class],
                [
                    new Cloze\Properties\Gaps\Text(
                        $DIC['refinery'],
                        $DIC['lng'],
                        $DIC['ui.factory']
                    ),
                    new Cloze\Properties\Gaps\Numeric(
                        $DIC['refinery'],
                        $DIC['lng'],
                        $DIC['ui.factory']
                    ),
                    new Cloze\Properties\Gaps\Select(
                        $DIC['refinery'],
                        $DIC['lng'],
                        $DIC['ui.factory']
                    ),
                    new Cloze\Properties\Gaps\LongMenu(
                        $DIC['refinery'],
                        $DIC['lng'],
                        $DIC['ui.factory']
                    )
                ]
            );
        $dic[Cloze\Properties\AnswerForm\Factory::class] = static fn($c): Cloze\Properties\AnswerForm\Factory
            => new Cloze\Properties\AnswerForm\Factory(
                $c[Cloze\Properties\ClozeText\Factory::class],
                $c[Cloze\Properties\Gaps\Factory::class]
            );
        $dic[Cloze\Persistence::class] = static fn($c): Cloze\Persistence
            => new Cloze\Persistence(
                new TableNameSpaceCore('cloze')
            );
        $dic[Cloze\Views\Edit::class] = static fn($c): Cloze\Views\Edit
            => new Cloze\Views\Edit(
                $DIC['lng'],
                $DIC['ui.factory'],
                $DIC['refinery'],
                $DIC['http'],
                $c[Cloze\Properties\AnswerForm\Factory::class],
                $c[Cloze\Properties\ClozeText\Factory::class],
                $c[Cloze\Properties\Gaps\Factory::class]
            );
        $dic[Cloze\Views\Participant::class] = static fn($c): Cloze\Views\Participant
            => new Cloze\Views\Participant();
        $dic[Cloze\Definition::class] = static fn($c): Cloze\Definition => new Cloze\Definition(
            $c[Cloze\Properties\AnswerForm\Factory::class],
            $c[Cloze\Persistence::class],
            [
                Cloze\Capabilities\Marking::class => new Cloze\Capabilities\Marking(),
                Cloze\Capabilities\Feedback::class => new Cloze\Capabilities\Feedback(),
                Cloze\Capabilities\Skills::class => new Cloze\Capabilities\Skills()
            ],
            $c[Cloze\Views\Edit::class],
            $c[Cloze\Views\Participant::class]
        );

        return $dic;
    }
}
