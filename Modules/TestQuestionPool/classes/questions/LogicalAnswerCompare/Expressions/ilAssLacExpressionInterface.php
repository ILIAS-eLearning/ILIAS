<?php
/**
 * Class ExpressionInterface
 *
 * Date: 25.03.13
 * Time: 15:43
 * @author Thomas Joußen <tjoussen@databay.de>
 */

interface ilAssLacExpressionInterface
{

    /**
     * Get the value of this Expression
     *
     * @return string
     */
    public function getValue();

    /**
     * Parses the delivered Value and sets the relevant information for an Expression as attributes
     *
     * @param string $value
     */
    public function parseValue($value);
}
