<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacAbstractComposite.php";

/**
 * Class AbstractOperation
 *
 * Date: 25.03.13
 * Time: 15:37
 * @author Thomas Joußen <tjoussen@databay.de>
 */
abstract class ilAssLacAbstractOperation extends ilAssLacAbstractComposite
{

    /**
     * @var bool
     */
    protected $negated = false;

    /**
     * @return string
     */
    abstract public function getPattern();

    /**
     * @param boolean $negated
     */
    public function setNegated($negated)
    {
        $this->negated = $negated;
    }

    /**
     * @return boolean
     */
    public function isNegated()
    {
        return $this->negated;
    }
}
