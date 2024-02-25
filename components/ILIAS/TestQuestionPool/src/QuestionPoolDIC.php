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

namespace ILIAS\TestQuestionPool;

use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;

use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionsDatabaseRepository;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\TestQuestionPool\Questions\Files\QuestionFiles;

use ILIAS\Test\Participants\ParticipantRepository;

class QuestionPoolDIC extends PimpleContainer
{
    public static ?self $dic = null;

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
        $dic['request_data_collector'] = static fn($c): RequestDataCollector =>
            new RequestDataCollector(
                $DIC->http(),
                $DIC['refinery'],
                $DIC['upload']
            );
        $dic['question.repo.suggestedsolutions'] = static fn($c): SuggestedSolutionsDatabaseRepository =>
            new SuggestedSolutionsDatabaseRepository($DIC['ilDB']);
        $dic['general_question_properties_repository'] = static fn($c): GeneralQuestionPropertiesRepository =>
            new GeneralQuestionPropertiesRepository(
                $DIC['ilDB'],
                $DIC['component.factory'],
                $DIC['lng']
            );
        $dic['question_files'] = fn($c): QuestionFiles =>
            new QuestionFiles();

        $dic['participant_repository'] = static fn($c): ParticipantRepository =>
            new ParticipantRepository($DIC['ilDB']);

        return $dic;
    }
}
