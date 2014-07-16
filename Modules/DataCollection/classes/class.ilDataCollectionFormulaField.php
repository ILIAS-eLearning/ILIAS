<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilDataCollectionRecordField.php';

/**
 * Class ilDataCollectionField
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDataCollectionFormulaField extends ilDataCollectionRecordField
{

    /**
     * @var string
     */
    protected $expression = '';

    /**
     * @var array
     */
    protected $field_properties = array();

    /**
     * @var string
     */
    protected $parsed_value = '';


    /**
     * @param ilDataCollectionRecord $record
     * @param ilDataCollectionField $field
     */
    public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field)
    {
        parent::__construct($record, $field);
        $this->field_properties = $field->getProperties();
        $this->expression = $this->field_properties[ilDataCollectionField::PROPERTYID_FORMULA_EXPRESSION];
    }


    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    protected function loadValue()
    {
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
    }

    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    public function doUpdate()
    {
    }

    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    public function doRead()
    {
    }

    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    public function delete()
    {
    }

    /**
     *
     * @return mixed|string
     */
    public function getFormInput()
    {
        return $this->parse();
    }

    public function getHTML()
    {
        return $this->parse();// . '<br><small>' . $this->expression . '</small>';
    }

    public function getExportValue()
    {
        return $this->parse();
    }

    public function getValue()
    {
        return $this->parse();
    }



    /**
     * Parse expression
     *
     * @return string
     */
    protected function parse()
    {
        if (!$this->parsed_value) {
            $parser = new ilDclExpressionParser($this->expression, $this->record, $this->field);
            try {
                $this->parsed_value = $parser->parse();
            } catch (ilException $e) {
                return $this->lng->txt('dcl_error_parsing_expression') . ' (' . $e->getMessage(). ')';
            }
        }
        return $this->parsed_value;
    }

}


/**
 * Class ilDclStack
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDclStack
{

    protected $stack = array();

    public function push($elem)
    {
        $this->stack[] = $elem;
    }

    public function pop()
    {
        if (!$this->isEmpty()) {
            $last_index = count($this->stack) - 1;
            $elem = $this->stack[$last_index];
            unset($this->stack[$last_index]);
            $this->stack = array_values($this->stack); // re-index
            return $elem;
        }
        return null;
    }

    public function top()
    {
        if (!$this->isEmpty()) {
            return $this->stack[count($this->stack) - 1];
        }
        return null;
    }

    public function isEmpty()
    {
        return !(bool)count($this->stack);
    }

    public function reset()
    {
        $this->stack = array();
    }

    public function count()
    {
        return count($this->stack);
    }

    public function debug()
    {
        echo "<pre>" . print_r($this->stack, 1) . "</pre>";
    }

}

/**
 * Class ilDclExpressionParser
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDclExpressionParser
{

    const N_DECIMALS = 1;

    /**
     * @var ilDataCollectionRecord
     */
    protected $record;

    /**
     * @var ilDataCollectionField
     */
    protected $field;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var array
     */
    protected static $operators = array(
        '+' => array('precedence' => 1),
        '-' => array('precedence' => 1),
        '*' => array('precedence' => 2),
        '/' => array('precedence' => 2),
        '^' => array('precedence' => 3),
    );

    /**
     * @var array
     */
    protected static $cache_tokens = array();

    /**
     * @var array
     */
    protected static $cache_fields = array();

    /**
     * @var array
     */
    protected static $cache_math_tokens = array();

    /**
     * @var array
     */
    protected static $cache_math_function_tokens = array();

    /**
     * @var array
     */
    protected static $functions = array(
        'SUM',
        'AVERAGE',
        'MIN',
        'MAX',
    );

    /**
     * @param string $expression
     * @param ilDataCollectionRecord $record
     * @param ilDataCollectionField $field
     */
    public function __construct($expression, ilDataCollectionRecord $record, ilDataCollectionField $field)
    {
        $this->expression = $expression;
        $this->record = $record;
        $this->field = $field;
    }


    /**
     * Parse expression and return result.
     * This method loops the tokens and checks if Token is of type string or math. Concatenates results
     * to produce resulting string of parsed expression.
     *
     * @throws ilException
     * @return string
     */
    public function parse()
    {
        if (isset(self::$cache_tokens[$this->field->getId()])) {
            $tokens = self::$cache_tokens[$this->field->getId()];
        } else {
            $tokens = ilDclTokenizer::getTokens($this->expression);
            self::$cache_tokens[$this->field->getId()] = $tokens;
        }
//        echo "<pre>" . print_r($tokens, 1) . "</pre>";
        $parsed = '';
        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }
            if ($this->isMathToken($token)) {
                $token = $this->calculateFunctions($token);
                $math_tokens = ilDclTokenizer::getMathTokens($token);
                $parsed .= $this->parseMath($this->substituteFieldValues($math_tokens));
            } else {
                // Token is a string, either a field placeholder [[Field name]] or a string starting with "
                if (strpos($token, '"') === 0) {
                    $parsed .= strip_tags(trim($token, '"'));
                } elseif (strpos($token, '[[') === 0) {
                    $parsed .= strip_tags($this->substituteFieldValue($token));
                } else {
                    throw new ilException("Unrecognized string token: '$token'");
                }
            }
        }
        return $parsed;
    }


    /**
     * @return array
     */
    public static function getOperators()
    {
        return self::$operators;
    }


    /**
     * @return array
     */
    public static function getFunctions()
    {
        return self::$functions;
    }


    /**
     * Check if a given token is a math expression
     *
     * @param string $token
     * @return bool
     */
    protected function isMathToken($token)
    {
        if (isset(self::$cache_math_tokens[$this->field->getId()][$token])) {
            return self::$cache_math_tokens[$this->field->getId()][$token];
        } else {
            if (strpos($token, '"') === 0) {
                return false;
            }
            $operators = array_keys(self::getOperators());
            $functions = self::getFunctions();
            $result = (bool) preg_match('#(\\' . implode("|\\", $operators) . '|' . implode('|', $functions) . ')#', $token);
            self::$cache_math_tokens[$this->field->getId()][$token] = $result;
            return $result;
        }
    }


    /**
     * Execute any math functions inside a token
     *
     * @param string $token
     * @return string
     */
    protected function calculateFunctions($token)
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
     *
     * @param $index
     * @param array $data
     * @return array
     */
    protected function getFunctionArgs($index, array $data)
    {
        $return = array(
            'function' => '',
            'args' => array(),
        );
        for ($i=1;$i<count($data);$i++) {
            $_data = $data[$i];
            if ($_data[$index]) {
                $function = $_data[$index];
                $args = explode(';', $data[$i+1][$index]);
                $return['function'] = $function;
                $return['args'] = $args;
                break;
            }
        }
        return $return;
    }


    /**
     * Given an array of tokens, replace each token that is a placeholder (e.g. [[Field name]]) with it's value
     *
     * @param array $tokens
     * @return array
     */
    protected function substituteFieldValues(array $tokens)
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
     *
     * @param string $placeholder
     * @throws ilException
     * @return string
     */
    protected function substituteFieldValue($placeholder)
    {
        if (isset(self::$cache_fields[$placeholder])) {
            $field = self::$cache_fields[$placeholder];
        } else {
            $table = ilDataCollectionCache::getTableCache($this->record->getTableId()); // TODO May need caching per table in future
            $field_title = preg_replace('#^\[\[(.*)\]\]#', "$1", $placeholder);
            $field = $table->getFieldByTitle($field_title);
            if ($field === null) {
                // Workaround for standardfields - title my be ID
                $field = $table->getField($field_title);
                if ($field === null) {
                    throw new ilException("Field with title '$field_title' not found");
                }
            }
            self::$cache_fields[$placeholder] = $field;
        }
        return $this->record->getRecordFieldHTML($field->getId());
    }


    /**
     * Parse a math expression
     *
     * @param array $tokens
     * @return null
     * @throws Exception
     */
    protected function parseMath(array $tokens)
    {
        $operators = self::$operators;
        $precedence = 0;
        $stack = new ilDclStack();
        $precedences = new ilDclStack();
        $in_bracket = false;
        foreach ($tokens as $token) {
            if (is_numeric($token) || $token === '(') {
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
                        $right = (float)$stack->pop();
                        $operator = $stack->pop();
                        $left = (float)$stack->pop();
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
                throw new Exception("Unrecognized token '$token'");
            }
//            $stack->debug();
        }
        // If one element is left on stack, we are done. Otherwise calculate
        if ($stack->count() == 1) {
            $result = $stack->pop();
            return (ctype_digit((string) $result)) ? $result : number_format($result, self::N_DECIMALS, '.', "'");
        } else {
            while ($stack->count() >= 3) {
                $right = $stack->pop();
                $operator = $stack->pop();
                $left = $stack->pop();
                $stack->push($this->calculate($operator, $left, $right));
            }
            $result = $stack->pop();
            return (ctype_digit((string) $result)) ? $result : number_format($result, self::N_DECIMALS, '.', "'");
        }
    }


    /**
     * Calculate a function with its arguments
     *
     * @param $function Function name to calculate
     * @param array $args Arguments of function
     * @return float|int|number
     * @throws ilException
     */
    protected function calculateFunction($function, array $args=array())
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
     * @param string $operator
     * @param float $left
     * @param float $right
     * @return float|number
     * @throws ilException
     */
    protected function calculate($operator, $left, $right)
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

/**
 * Class ilDclTokenizer
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDclTokenizer
{


    /**
     * Split expression by & (ignore escaped &-symbols with backslash)
     *
     * @param string $expression Global expression to parse
     * @return array
     */
    public static function getTokens($expression)
    {
        $expression = ltrim($expression, '=');
        $expression = trim($expression);
        $tokens = preg_split('#[^\\\\]&#', $expression);
        return array_map('trim', $tokens);
    }


    /**
     * Generate tokens for a math expression
     *
     * @param string $math_expression Expression of type math
     * @return array
     */
    public static function getMathTokens($math_expression)
    {
        $operators = array_keys(ilDclExpressionParser::getOperators());
        $pattern = '#((^\[\[)[\d\.]+)|(\(|\)|\\' . implode("|\\", $operators) . ')#';
        $tokens = preg_split($pattern, $math_expression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        return array_map('trim', $tokens);
    }
}


?>