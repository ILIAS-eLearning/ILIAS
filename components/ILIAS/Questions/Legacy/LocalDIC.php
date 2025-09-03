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
use ILIAS\Questions\AnswerFormTypes\Factory as AnswerFormTypesFactory;
use ILIAS\Questions\Presentation\Edit;
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
        $dic[Edit::class] = static fn($c): Edit =>
            new Edit(
                $DIC['lng'],
                $DIC['ilUser'],
                $DIC['refinery'],
                $DIC['ui.factory'],
                $DIC['ui.renderer'],
                $DIC['global_screen'],
                $DIC['ilCtrl'],
                $DIC['http'],
                $DIC->uiService(),
                new DataFactory(),
                new QuestionsRepository(
                    $DIC['ilDB'],
                    new UuidFactory(),
                    new AnswerFormTypesFactory([
                        new Cloze\Type(
                            new Cloze\Persistence(
                                new TableNameSpaceCore('cloze')
                            ),
                            new Cloze\Marking(),
                            new Cloze\Views\Edit(
                                $DIC['lng'],
                                $DIC['ui.factory'],
                                $DIC['refinery'],
                                $DIC['http']->request(),
                                new DataFactory()
                            ),
                            new Cloze\Views\Participant()
                        )
                    ])
                )
            );

        return $dic;
    }
}
