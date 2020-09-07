<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAbstractOperation.php';

/**
 * Class Lesser
 *
 * Date: 25.03.13
 * Time: 14:57
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacLesserOperation extends ilAssLacAbstractOperation
{

    /**
     * @var string
     */
    public static $pattern = "<";

    public function getDescription()
    {
        return "mit weniger als ";
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return self::$pattern;
    }
}
