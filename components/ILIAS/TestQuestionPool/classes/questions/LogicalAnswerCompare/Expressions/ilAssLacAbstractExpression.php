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
 * Class AbstractExpression
 *
 * Date: 25.03.13
 * Time: 15:42
 * @author Thomas Joußen <tjoussen@databay.de>
 */
abstract class ilAssLacAbstractExpression extends ilAssLacAbstractComposite implements ilAssLacExpressionInterface
{
    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    protected function getPattern(): string
    {
        return '/-?[0-9\.]+/';
    }

    /**
     * Parses the delivered Value and sets the relevant information for an Expression as attributes
     *
     * @param string $value
     */
    public function parseValue($value): void
    {
        $result = array();
        preg_match_all($this->getPattern(), $value, $result);
        $this->setMatches($result);
    }

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    abstract protected function setMatches($matches);
}
