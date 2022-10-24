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
    public function getValue(): string;

    /**
     * Parses the delivered Value and sets the relevant information for an Expression as attributes
     *
     * @param string $value
     */
    public function parseValue($value);
}
