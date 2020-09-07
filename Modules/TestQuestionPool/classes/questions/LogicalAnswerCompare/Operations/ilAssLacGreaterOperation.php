<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAbstractOperation.php';

/**
 * Class Greater
 *
 * Date: 25.03.13
 * Time: 14:57
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacGreaterOperation extends ilAssLacAbstractOperation
{

    /**
     * @var string
     */
    public static $pattern = ">";

    public function getDescription()
    {
        return "mit mehr als ";
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return self::$pattern;
    }
}
