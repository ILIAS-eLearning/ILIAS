<?php declare(strict_types=1);

use Pimple\Container;
use ILIAS\TA\Questions\assQuestionSuggestedSolutionsDatabaseRepository;

use ILIAS\TA\Questions\assQuestionFactory;

class ilQuestionPoolDIC
{
    public static ?Container $dic = null;

    public static function dic() : Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }
        return self::$dic;
    }

    protected static function buildDIC() : Container
    {
        global $DIC;
        $dic = $DIC;
        $container = new Container();

        $dic['question.repo.suggestedsolutions'] = function ($c) use ($dic) : assQuestionSuggestedSolutionsDatabaseRepository {
            return new assQuestionSuggestedSolutionsDatabaseRepository($dic['ilDB']);
        };

        return $dic;
    }
}
