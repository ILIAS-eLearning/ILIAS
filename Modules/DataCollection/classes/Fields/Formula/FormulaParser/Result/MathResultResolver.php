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

namespace ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Result;

use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Substitution\FieldSubstitution;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Token\Token;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Token\Tokenizer;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Functions;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Stack;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Operators;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Result\Result\Result;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Result\Result\IntegerResult;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Result\Result\DateResult;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Token\MathToken;

class MathResultResolver implements ResultResolver
{
    private \ilLanguage $lng;

    public function __construct(
        private FieldSubstitution $substitution,
        private Tokenizer $tokenizer,
    ) {
    }

    public function resolve(Token $token): Result
    {
        $calculated_token = $this->calculateFunctions($token);
        $math_tokens = $this->tokenizer->tokenizeMath($calculated_token->getValue());

        $substituted = $this->substitution->substituteFieldValues($math_tokens);
        [$result, $last_operator] = $this->parseMath($substituted);

        $from_function = $calculated_token->getFromFunction();

        if ($this->hasDateFieldsInMathTokens($math_tokens, $token)) {
            return new DateResult(
                (string) $result,
                $from_function,
                $last_operator
            );
        }

        return new IntegerResult(
            (string) $result,
            $from_function
        );
    }

    private function hasDateFieldsInMathTokens(array $math_tokens, Token $original_token): bool
    {
        foreach ($math_tokens as $math_token) {
            if (str_starts_with($math_token->getValue(), Tokenizer::FIELD_OPENER)) {
                $field = $this->substitution->getFieldFromPlaceholder($math_token->getValue());
                if ($field->getDatatypeId() === \ilDclDatatype::INPUTFORMAT_DATETIME) {
                    return true;
                }
            }
        }

        // fallback to original token
        $tokens = $this->tokenizer->tokenize($original_token->getValue());
        foreach ($tokens as $token) {
            // find placeholders in token-values using regex, placeholders start with [[ and end with ]], there can be multiple
            if (preg_match_all('/\[\[(.*?)\]\]/', $token->getValue(), $matches)) {
                foreach ($matches[1] as $match) {
                    $field = $this->substitution->getFieldFromPlaceholder($match);
                    if ($field->getDatatypeId() === \ilDclDatatype::INPUTFORMAT_DATETIME) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function parseMath(array $tokens): array
    {
        $operators = array_map(
            static fn(Operators $operator): string => $operator->value,
            Tokenizer::$operators
        );
        $precedence = 0;
        $stack = new Stack();
        $precedences = new Stack();
        $in_bracket = false;
        foreach ($tokens as $token) {
            if (is_string($token)) {
                $token = new MathToken($token);
            }

            // we use the tokens value
            $token = $token->getValue() === '' ? '0' : $token->getValue();

            if (is_numeric($token) || $token === '(') {
                $stack->push($token);
                if ($token === '(') {
                    $in_bracket = true;
                }
            } elseif (in_array($token, $operators)) {
                $last_operator = Operators::from($token);
                $new_precedence = $last_operator->getPrecedence();
                if ($new_precedence > $precedence || $in_bracket) {
                    // Precedence of operator is higher, push operator on stack
                    $stack->push($token);
                    $precedences->push($new_precedence);
                    $precedence = $new_precedence;
                } else {
                    // Precedence is equal or lower, calculate result on stack
                    while ($new_precedence <= $precedence && $stack->count() > 1) {
                        $right = (float) $stack->pop();
                        $operator = $stack->pop();
                        $left = (float) $stack->pop();
                        $result = $this->calculate($operator, $left, $right);
                        $stack->push($result);
                        $precedence = $precedences->pop();
                    }
                    $stack->push($token);
                    $precedence = $new_precedence;
                    $precedences->push($new_precedence);
                }
            } elseif ($token === ')') {
                // Need to calculate stack back to opening bracket
                $_tokens = [];
                $elem = $stack->pop();
                while ($elem !== '(' && !$stack->isEmpty()) {
                    $_tokens[] = $elem;
                    $elem = $stack->pop();
                }
                // Get result within brackets recursive and push to stack
                $stack->push($this->parseMath(array_reverse($_tokens)));
                $in_bracket = false;
            } else {
                throw new \ilException("Unrecognized token '$token'");
            }
        }
        // If one element is left on stack, we are done. Otherwise calculate
        if ($stack->count() === 1) {
            $result = $stack->pop();

            $value = (ctype_digit((string) $result)) ? $result : number_format($result, 2, '.', "'");
            return [$value, $last_operator ?? null];
        }

        while ($stack->count() >= 2) {
            $right = $stack->pop();
            $operator = $stack->pop();
            $left = $stack->count() ? $stack->pop() : 0;
            $stack->push($this->calculate($operator, $left, $right));
        }
        return [$stack->pop(), $last_operator];
    }

    protected function calculateFunctions(Token $token): Token
    {
        $pattern = '#';
        $functions = array_map(
            static fn(Functions $function): string => $function->value,
            Tokenizer::$functions
        );

        foreach ($functions as $function) {
            $pattern .= "($function)\\(([^)]*)\\)|";
        }
        if (!preg_match_all(rtrim($pattern, '|') . '#', $token->getValue(), $result)) {
            // No functions found inside token, just return token again
            return $token;
        }
        $token_value = $token->getValue();
        // Function found inside token, calculate!
        foreach ($result[0] as $k => $to_replace) {
            $function_args = $this->getFunctionArgs($k, $result);
            $function = $function_args['function'];
            $args = $this->substitution->substituteFieldValues($function_args['args']);
            $token_value = str_replace($to_replace, (string) $this->calculateFunction($function, $args), $token_value);
        }

        return new MathToken(
            $token_value,
            Functions::tryFrom($function ?? null)
        );
    }

    /**
     * Helper method to return the function and its arguments from a preg_replace_all $result array
     */
    protected function getFunctionArgs(int $index, array $data): array
    {
        $return = [
            'function' => '',
            'args' => [],
        ];
        for ($i = 1; $i < count($data); $i++) {
            $_data = $data[$i];
            if ($_data[$index]) {
                $function = $_data[$index];
                $args = explode(';', $data[$i + 1][$index]);
                $return['function'] = $function;
                $return['args'] = $args;
                break;
            }
        }

        return $return;
    }

    protected function calculateFunction(string $function, array $args = [])
    {
        $args = array_map(function (Token $arg) {
            return (float) $arg->getValue();
        }, $args);

        switch ($function) {
            case 'AVERAGE':
                $count = count($args);
                $array_sum = array_sum($args);

                return ($count > 0) ? $array_sum / $count : 0;
            case 'SUM':
                return array_sum($args);
            case 'MIN':
                return min($args);
            case 'MAX':
                return max($args);
            default:
                throw new ilException("Unrecognized function '$function'");
        }
    }

    /**
     * @param string    $operator
     * @param float|int $left
     * @param float|int $right
     * @return float|int
     * @throws ilException
     */
    protected function calculate(string $operator, $left, $right)
    {
        switch ($operator) {
            case '+':
                $result = $left + $right;
                break;
            case '-':
                $result = $left - $right;
                break;
            case '*':
                $result = $left * $right;
                break;
            case '/':
                $result = ($right == 0) ? 0 : $left / $right;
                break;
            case '^':
                $result = pow($left, $right);
                break;
            default:
                throw new \ilException("Unrecognized operator '$operator'");
        }

        return $result;
    }

}
