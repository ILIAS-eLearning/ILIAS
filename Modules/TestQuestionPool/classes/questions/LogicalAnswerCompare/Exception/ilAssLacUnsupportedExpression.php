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
 * Class UnsupportedExpression
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacUnsupportedExpression extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;

        parent::__construct(sprintf(
            'The expression "%s" is not supported',
            $this->getExpression()
        ));
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng): string
    {
        return sprintf(
            $lng->txt("ass_lac_expression_not_supported"),
            $this->getExpression()
        );
    }
}
