<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAbstractOperation.php';

/**
 * Class NotEquals
 *
 * Date: 25.03.13
 * Time: 14:57
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacNotEqualsOperation extends ilAssLacAbstractOperation
{

    /**
     * @var string
     */
    public static $pattern = "<>";

    public function getDescription()
    {
        return "nicht mit ";
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return self::$pattern;
    }
}
