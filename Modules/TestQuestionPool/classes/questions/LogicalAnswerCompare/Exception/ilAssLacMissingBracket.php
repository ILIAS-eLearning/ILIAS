<?php

require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacException.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacFormAlertProvider.php';

/**
 * Class ilAssLacAnswerIndexNotExist
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacMissingBracket extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $bracket;

    /**
     * @param string $bracket
     */
    public function __construct($bracket)
    {
        $this->bracket = $bracket;

        parent::__construct(sprintf(
            'There is a bracket "%s" missing in the condition',
            $this->getBracket()
        ));
    }

    /**
     * @return string
     */
    public function getBracket()
    {
        return $this->bracket;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng)
    {
        return sprintf(
            $lng->txt("ass_lac_missing_bracket"),
            $this->getBracket()
        );
    }
}
