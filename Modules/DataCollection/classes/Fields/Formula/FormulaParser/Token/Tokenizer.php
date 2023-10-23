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

declare(strict_types=1);

namespace ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Token;

use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Operators;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Functions;

class Tokenizer
{
    public static array $operators = [
        Operators::ADDITION,
        Operators::SUBTRACTION,
        Operators::MULTIPLICATION,
        Operators::DIVISION,
        Operators::POWER
    ];

    public static array $functions = [
        Functions::SUM,
        Functions::AVERAGE,
        Functions::MIN,
        Functions::MAX
    ];

    public const FIELD_OPENER = '[[';

    /**
     * @param array $return
     * @return Token[]
     */
    protected function valuesToTokens(array $return): array
    {
        return array_map(function (string $token): Token {
            if ($this->isMathToken($token)) {
                return new MathToken(trim($token));
            }
            return new Token(trim($token));
        }, $return);
    }

    /**
     * Split expression by & (ignore escaped &-symbols with backslash)
     * @param string $expression Global expression to parse
     * @return Token[]
     */
    public function tokenize(string $expression): array
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

        return $this->valuesToTokens($return);
    }

    private function isMathToken(string $token): bool
    {
        $operators = array_map(
            static fn(Operators $operator): string => $operator->value,
            self::$operators
        );

        $functions = array_map(
            static fn(Functions $functions): string => $functions->value,
            self::$functions
        );

        $result = (bool) preg_match(
            '#(\\' . implode("|\\", $operators) . '|' . implode('|', $functions) . ')#',
            $token
        );

        return $result;
    }

    /**
     * Generate tokens for a math expression
     * @param string $math_expression Expression of type math
     * @return Token[]
     */
    public function tokenizeMath(string $math_expression): array
    {
        $operators = array_map(
            static fn(Operators $operator): string => $operator->value,
            self::$operators
        );
        $pattern = '#((^\[\[)[\d\.]+)|(\(|\)|\\' . implode("|\\", $operators) . ')#';
        $tokens = preg_split($pattern, $math_expression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return $this->valuesToTokens($tokens);
    }
}
