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
 * Class ilDclExpressionParser
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclExpressionParser
{
    public const N_DECIMALS = 1;
    public const SCIENTIFIC_NOTATION_UPPER = 1000000000000;
    public const SCIENTIFIC_NOTATION_LOWER = 0.000000001;

    protected ilDclBaseRecordModel $record;
    protected ilDclBaseFieldModel $field;
    protected string $expression;
    protected static array $operators
        = array(
            '+' => array('precedence' => 1),
            '-' => array('precedence' => 1),
            '*' => array('precedence' => 2),
            '/' => array('precedence' => 2),
            '^' => array('precedence' => 3),
        );
    protected static array $cache_tokens = array();
    protected static array $cache_fields = array();
    protected static array $cache_math_tokens = array();
    protected static array $cache_math_function_tokens = array();
    protected static array $functions
        = array(
            'SUM',
            'AVERAGE',
            'MIN',
            'MAX',
        );

    public function __construct(string $expression, ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        $this->expression = $expression;
        $this->record = $record;
        $this->field = $field;
    }

    /**
     * Parse expression and return result.
     * This method loops the tokens and checks if Token is of type string or math. Concatenates results
     * to produce resulting string of parsed expression.
     * @throws ilException
     */
    public function parse(): string
    {
        if (isset(self::$cache_tokens[$this->field->getId()])) {
            $tokens = self::$cache_tokens[$this->field->getId()];
        } else {
            $tokens = ilDclTokenizer::getTokens($this->expression);
            self::$cache_tokens[$this->field->getId()] = $tokens;
        }
        $parsed = '';
        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }
            if ($this->isMathToken($token)) {
                $token = $this->calculateFunctions($token);
                $math_tokens = ilDclTokenizer::getMathTokens($token);
                $value = $this->parseMath($this->substituteFieldValues($math_tokens));

                $value = $this->formatScientific($value);

                $parsed .= $value;
            } else {
                // Token is a string, either a field placeholder [[Field name]] or a string starting with "
                if (strpos($token, '"') === 0) {
                    $parsed .= strip_tags(trim($token, '"'));
                } elseif (strpos($token, '[[') === 0) {
                    $parsed .= trim(strip_tags($this->substituteFieldValue($token)));
                } else {
                    throw new ilException("Unrecognized string token: '$token'");
                }
            }
        }

        return $parsed;
    }

    /**
     * @param float|int $value
     * @return string|int
     */
    protected function formatScientific($value)
    {
        if (abs($value) >= self::SCIENTIFIC_NOTATION_UPPER) {
            return sprintf("%e", $value);
        }
        if (abs($value) <= self::SCIENTIFIC_NOTATION_LOWER && $value != 0) {
            return sprintf("%e", $value);
        }
        return $value;
    }

    public static function getOperators(): array
    {
        return self::$operators;
    }

    public static function getFunctions(): array
    {
        return self::$functions;
    }

    /**
     * Check if a given token is a math expression
     */
    protected function isMathToken(string $token): bool
    {
        if (isset(self::$cache_math_tokens[$this->field->getId()][$token])) {
            return self::$cache_math_tokens[$this->field->getId()][$token];
        } else {
            if (strpos($token, '"') === 0) {
                return false;
            }
            $operators = array_keys(self::getOperators());
            $functions = self::getFunctions();
            $result = (bool) preg_match(
                '#(\\' . implode("|\\", $operators) . '|' . implode('|', $functions) . ')#',
                $token
            );
            self::$cache_math_tokens[$this->field->getId()][$token] = $result;

            return $result;
        }
    }

    /**
     * Execute any math functions inside a token
     */
    protected function calculateFunctions(string $token): string
    {
        if (isset(self::$cache_math_function_tokens[$this->field->getId()][$token])) {
            $result = self::$cache_math_function_tokens[$this->field->getId()][$token];
            if ($result === false) {
                return $token;
            }
        } else {
            $pattern = '#';
            foreach (self::getFunctions() as $function) {
                $pattern .= "($function)\\(([^)]*)\\)|";
            }
            if (!preg_match_all(rtrim($pattern, '|') . '#', $token, $result)) {
                // No functions found inside token, just return token again
                self::$cache_math_function_tokens[$this->field->getId()][$token] = false;

                return $token;
            }
        }
        // Function found inside token, calculate!
        foreach ($result[0] as $k => $to_replace) {
            $function_args = $this->getFunctionArgs($k, $result);
            $function = $function_args['function'];
            $args = $this->substituteFieldValues($function_args['args']);
            $token = str_replace($to_replace, $this->calculateFunction($function, $args), $token);
        }

        return $token;
    }

    /**
     * Helper method to return the function and its arguments from a preg_replace_all $result array
     */
    protected function getFunctionArgs(int $index, array $data): array
    {
        $return = array(
            'function' => '',
            'args' => array(),
        );
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

    /**
     * Given an array of tokens, replace each token that is a placeholder (e.g. [[Field name]]) with it's value
     */
    protected function substituteFieldValues(array $tokens): array
    {
        $replaced = array();
        foreach ($tokens as $token) {
            if (strpos($token, '[[') === 0) {
                $replaced[] = $this->substituteFieldValue($token);
            } else {
                $replaced[] = $token;
            }
        }

        return $replaced;
    }

    /**
     * Substitute field values in placehoders like [[Field Title]] from current record
     * @throws ilException
     */
    protected function substituteFieldValue(string $placeholder): string
    {
        if (isset(self::$cache_fields[$placeholder])) {
            $field = self::$cache_fields[$placeholder];
        } else {
            $table = ilDclCache::getTableCache($this->record->getTableId()); // TODO May need caching per table in future
            $field_title = preg_replace('#^\[\[(.*)\]\]#', "$1", $placeholder);
            $field = $table->getFieldByTitle($field_title);
            if ($field === null) {
                // Workaround for standardfields - title my be ID
                $field = $table->getField($field_title);
                if ($field === null) {
                    global $DIC;
                    $lng = $DIC['lng'];
                    /**
                     * @var $lng ilLanguage
                     */
                    $lng->loadLanguageModule('dcl');
                    //					throw new ilException("Field with title '$field_title' not found");
                    throw new ilException(sprintf($lng->txt('dcl_err_formula_field_not_found'), $field_title));
                }
            }
            self::$cache_fields[$placeholder] = $field;
        }

        return $this->record->getRecordFieldFormulaValue($field->getId());
    }

    /**
     * Parse a math expression
     * @throws Exception
     */
    protected function parseMath(array $tokens): ?string
    {
        $operators = self::$operators;
        $precedence = 0;
        $stack = new ilDclStack();
        $precedences = new ilDclStack();
        $in_bracket = false;
        foreach ($tokens as $token) {
            if (empty($token) or is_null($token)) {
                $token = 0;
            }
            if (is_numeric($token) or $token === '(') {
                $stack->push($token);
                if ($token === '(') {
                    $in_bracket = true;
                }
            } elseif (in_array($token, array_keys($operators))) {
                $new_precedence = $operators[$token]['precedence'];
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
                $_tokens = array();
                $elem = $stack->pop();
                while ($elem !== '(' && !$stack->isEmpty()) {
                    $_tokens[] = $elem;
                    $elem = $stack->pop();
                }
                // Get result within brackets recursive and push to stack
                $stack->push($this->parseMath(array_reverse($_tokens)));
                $in_bracket = false;
            } else {
                throw new ilException("Unrecognized token '$token'");
            }
            // $stack->debug();
        }
        // If one element is left on stack, we are done. Otherwise calculate
        if ($stack->count() == 1) {
            $result = $stack->pop();

            return (ctype_digit((string) $result)) ? $result : number_format($result, self::N_DECIMALS, '.', "'");
        } else {
            while ($stack->count() >= 2) {
                $right = $stack->pop();
                $operator = $stack->pop();
                $left = $stack->count() ? $stack->pop() : 0;
                $stack->push($this->calculate($operator, $left, $right));
            }
            $result = $stack->pop();

            return $result;
        }
    }

    /**
     * Calculate a function with its arguments
     * @return float|int|number
     * @throws ilException
     */
    protected function calculateFunction(string $function, array $args = array())
    {
        switch ($function) {
            case 'AVERAGE':
                $count = count($args);

                return ($count) ? array_sum($args) / $count : 0;
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
                throw new ilException("Unrecognized operator '$operator'");
        }

        return $result;
    }
}
