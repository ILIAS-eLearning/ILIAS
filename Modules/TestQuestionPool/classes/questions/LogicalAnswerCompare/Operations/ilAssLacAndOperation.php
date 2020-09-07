<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAbstractOperation.php';

/**
 * Class AndOperation
 *
 * Date: 25.03.13
 * Time: 14:58
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacAndOperation extends ilAssLacAbstractOperation
{

    /**
     * @var string
     */
    public static $pattern = "&";

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return "und ";
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return self::$pattern;
    }
}
