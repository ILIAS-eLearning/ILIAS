<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAbstractOperation.php';

/**
 * Class LesserOrEquals
 *
 * Date: 25.03.13
 * Time: 14:58
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacLesserOrEqualsOperation extends ilAssLacAbstractOperation
{

    /**
     * @var string
     */
    public static $pattern = "<=";

    public function getDescription()
    {
        return "mit weniger oder gleich ";
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return self::$pattern;
    }
}
