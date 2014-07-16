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
            $parser = new ilDclExpressionParser($this->expression, $this->record);
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
     * @param string $expression
     * @param ilDataCollectionRecord $record
     */
    public function __construct($expression, ilDataCollectionRecord $record)
    {
        $this->expression = $expression;
        $this->record = $record;
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
        $tokens = ilDclTokenizer::getTokens($this->expression);
//        echo "<pre>" . print_r($tokens, 1) . "</pre>";
        $parsed = '';
        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }
            if ($this->isMathToken($token)) {
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
     * Check if a given token is a math expression
     *
     * @param string $token
     * @return bool
     */
    protected function isMathToken($token)
    {
        if (strpos($token, '"') === 0) {
            return false;
        }
        $operators = array_keys(self::getOperators());
        return (bool) preg_match('#(\\' . implode("|\\", $operators) . ')#', $token);
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
     * Substitute field values in placehoders like [[Field Title]]
     *
     * @param string $placeholder
     * @throws ilException
     * @return string
     */
    protected function substituteFieldValue($placeholder)
    {
        $table = ilDataCollectionCache::getTableCache($this->record->getTableId());
        $field_title = preg_replace('#^\[\[(.*)\]\]#', "$1", $placeholder);
        $field = $table->getFieldByTitle($field_title);
        if ($field === null) {
            throw new ilException("Field with title '$field_title' either not found or not compatible");
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