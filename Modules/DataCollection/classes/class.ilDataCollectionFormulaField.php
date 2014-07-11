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
     * @var array
     */
    protected static $compatible_datatypes = array(
        ilDataCollectionDatatype::INPUTFORMAT_NUMBER,
        ilDataCollectionDatatype::INPUTFORMAT_RATING,
    );

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
     * @return array
     */
    public static function getCompatibleDatatypes() {
        return self::$compatible_datatypes;
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
                return $this->lng->txt('dcl_error_parsing_expression');
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
     * @var ilDclTokenizer
     */
    protected $tokenizer;


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
        $this->tokenizer = new ilDclTokenizer($expression);
        $this->record = $record;
    }


    /**
     * Parse expression and return result.
     * This method loops the tokens and checks if Token is of type string or math. Math means that the
     * token is either a numeric value, an operator or brackets.
     *
     * @throws ilException
     * @return string
     */
    public function parse()
    {
        $tokens = $this->tokenizer->getTokens();
        $math_tokens = array();
        $parsed = '';
        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }
            if (strpos($token, '"') === 0) {
                // Token is a string
                if (count($math_tokens)) {
                    $parsed .= $this->parseMath($math_tokens);
                    $math_tokens = array();
                }
                $parsed .= trim($token, '"');
            } elseif (strpos($token, '[[') === 0) {
                // Token is a placeholder -> Replace with field value
                $table = ilDataCollectionCache::getTableCache($this->record->getTableId());
                $field_title = preg_replace('#^\[\[(.*)\]\]#', "$1", $token);
                $field = $table->getFieldByTitle($field_title);
                if ($field === null || !in_array($field->getDatatypeId(), ilDataCollectionFormulaField::getCompatibleDatatypes())) {
                    throw new ilException("Field with title '$field_title' either not found or not compatible");
                }
                if ($field->isStandardField()) {
                    throw new ilException("Standard-Fields not supported by the formula field");
                }
                // Just to be absolutely sure we got object...
                $record_field = $this->record->getRecordField($field->getId());
                if (is_object($record_field)) {
                    $math_tokens[] = (float) $record_field->getValue();
                } else {
                    throw new ilException("Could not load RecordField object");
                }
            } else {
                // Assume that token is either numeric or operator
                $math_tokens[] = $token;
            }
        }
        if (count($math_tokens)) {
            $parsed .= $this->parseMath($math_tokens);
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
     * @var array
     */
    protected $tokens = array();

    /**
     * @var string
     */
    protected $expression = '';

    /**
     * @param $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
        $this->tokenize();
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }


    /**
     * Generate array of tokens
     *
     * Example:
     * Expression: [[Feld 1]] + 20 * 3 & " concatenated String " &  [[Feld3]]
     *
     * Translates to:
     * array([[Feld 1]], +, 20, *, 3, "concatenated String", [[Feld3]])
     */
    protected function tokenize()
    {
        if (!count($this->tokens)) {
            $operators = array_keys(ilDclExpressionParser::getOperators());
            $pattern = '#((^\[\[)[\d\.]+)|&|(\(|\)|\\' . implode("|\\", $operators) . ')#';
            $tokens = preg_split($pattern, $this->expression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $this->tokens = array_map('trim', $tokens);
        }
    }
}


?>