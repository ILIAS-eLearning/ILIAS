<?php

require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacException.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacFormAlertProvider.php';

/**
 * Class ConditionParserException
 *
 * Date: 02.04.14
 * Time: 15:40
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacConditionParserException extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var int
     */
    protected $column;

    /**
     * @param int $column
     */
    public function __construct($column)
    {
        $this->column = $column;

        parent::__construct(sprintf(
            'The expression at position "%s" is not valid',
            $this->getColumn()
        ));
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng)
    {
        return sprintf(
            $lng->txt("ass_lac_invalid_statement"),
            $this->getColumn()
        );
    }
}
