<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAbstractOperation.php';

/**
 * Class Equals
 *
 * Date: 25.03.13
 * Time: 14:57
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacEqualsOperation extends ilAssLacAbstractOperation
{

    /**
     * @var string
     */
    public static $pattern = "=";

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return 'mit genau ';
    }

    public function getPattern()
    {
        return self::$pattern;
    }
}
