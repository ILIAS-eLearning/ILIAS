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

        $matches = [];
        //Match all & inside [] (e.g. [[Metadaten & OER]])
        preg_match_all("/\[\[[^\]]*&[^\]]*\]\]/ui", $expression, $matches);
        $matches_inside_brackets = $matches[0];
        $replace_random = sha1("replacement_string");


        //Replace those & with a set of unprobable chars, to be ignored by the following selection of tokens
        foreach ($matches_inside_brackets as $match) {
            if (!$match) {
                continue;
            }
            $match_save = str_replace("&", $replace_random, $match);
            $expression = str_replace($match, $match_save, $expression);
        }

        //var_dump($expression);
        preg_match_all("/([^\\\\&]|\\\\&)*/ui", $expression, $matches);
        $results = $matches[0];

        $return = [];
        foreach ($results as $result) {
            if (!$result) {
                continue;
            }
            $replace = str_ireplace('\&', '&', $result);

            //Replace those & before replaced chars back
            $return[] = str_replace($replace_random, "&", $replace);

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
        $tokens = preg_split($pattern, $math_expression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $tokens);
    }
}
