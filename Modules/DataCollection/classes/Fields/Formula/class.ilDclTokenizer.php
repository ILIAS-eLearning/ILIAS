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
 ********************************************************************
 */

/**
 * Class ilDclTokenizer
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclTokenizer
{
    /**
     * Split expression by & (ignore escaped &-symbols with backslash)
     * @param string $expression Global expression to parse
     */
    public static function getTokens(string $expression): array
    {
        $expression = ltrim($expression, '=');
        $expression = trim($expression);
        preg_match_all("/([^\\\\&]|\\\\&)*/ui", $expression, $matches);

        $results = $matches[0];

        $return = array();
        foreach ($results as $r) {
            if (!$r) {
                continue;
            }
            $return[] = str_ireplace('\&', '&', $r);
        }

        return array_map('trim', $return);
    }

    /**
     * Generate tokens for a math expression
     * @param string $math_expression Expression of type math
     */
    public static function getMathTokens(string $math_expression): array
    {
        $operators = array_keys(ilDclExpressionParser::getOperators());
        $pattern = '#((^\[\[)[\d\.]+)|(\(|\)|\\' . implode("|\\", $operators) . ')#';
        $tokens = preg_split($pattern, $math_expression, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $tokens);
    }
}
