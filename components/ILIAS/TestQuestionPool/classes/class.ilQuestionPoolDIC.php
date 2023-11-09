<?php

declare(strict_types=1);
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

use Pimple\Container;
use ILIAS\TA\Questions\assQuestionSuggestedSolutionsDatabaseRepository;

use ILIAS\TA\Questions\assQuestionFactory;

class ilQuestionPoolDIC
{
    public static ?Container $dic = null;

    public static function dic(): Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }
        return self::$dic;
    }

    protected static function buildDIC(): Container
    {
        global $DIC;
        $dic = $DIC;
        $container = new Container();

        $dic['question.repo.suggestedsolutions'] = function ($c) use ($dic): assQuestionSuggestedSolutionsDatabaseRepository {
            return new assQuestionSuggestedSolutionsDatabaseRepository($dic['ilDB']);
        };

        return $dic;
    }
}
