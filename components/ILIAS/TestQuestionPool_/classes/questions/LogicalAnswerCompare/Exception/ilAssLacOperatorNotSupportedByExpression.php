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
 * Class OperatorNotSupportedByExpression
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacOperatorNotSupportedByExpression extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @param string $expression
     * @param string $operator
     */
    public function __construct($expression, $operator)
    {
        $this->expression = $expression;
        $this->operator = $operator;

        parent::__construct(sprintf(
            'The expression "%s" is not supported by the operator "%s"',
            $this->getExpression(),
            $this->getOperator()
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
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng): string
    {
        return sprintf(
            $lng->txt("ass_lac_operator_not_supported_by_expression"),
            $this->getOperator(),
            $this->getExpression()
        );
    }
}
