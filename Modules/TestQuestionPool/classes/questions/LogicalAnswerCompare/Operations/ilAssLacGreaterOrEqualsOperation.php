<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAbstractOperation.php';

/**
 * Class GreaterOrEquals
 *
 * Date: 25.03.13
 * Time: 14:58
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacGreaterOrEqualsOperation extends ilAssLacAbstractOperation
{

    /**
     * @var string
     */
    public static $pattern = ">=";

    public function getDescription()
    {
        return "mit mehr oder genau ";
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return self::$pattern;
    }
}
