<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class UnableToParseCondition
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacUnableToParseCondition extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $condition;


    /**
     * @param string $expression
     * @param int    $question_index
     */
    public function __construct($condition)
    {
        $this->condition = $condition;

        parent::__construct(sprintf(
            'The parser is unable to parse the condition "%s"',
            $this->getCondition()
        ));
    }

    /**
     * @return string
     */
    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng): string
    {
        return sprintf(
            $lng->txt("ass_lac_unable_to_parse_condition"),
            $this->getCondition()
        );
    }
}
