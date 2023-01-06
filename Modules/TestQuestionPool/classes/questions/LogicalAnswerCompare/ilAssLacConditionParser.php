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
 * Class ConditionParser
 * @package ConditionParser
 *
 * Date: 22.03.13
 * Time: 13:54
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacConditionParser
{
    /**
     * The condition which should be parsed into a ParserComposite to match a branch condition
     *
     * @var string
     */
    protected $condition;

    /**
     * The expressions which are be matched by the regular expression ConditionParser::$regex_expression in the condition
     *
     * @see Parser::$regex_expression
     * @var array
     */
    protected $expressions;

    /**
     * The operators which are be matched by the regular expression ConditionParser::$regex_operator in the condition
     *
     * @see Parser::$regex_operator
     * @var array
     */
    protected $operators;

    /**
     * The parser index to save the current position in the condition parser
     *
     * @var int
     */
    protected $index;

    /**
     * Counts the number of spaces in a condition
     *
     * @var int
     */
    protected $spaces;

    /**
     * Construct requirements
     */
    public function __construct()
    {
        include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Factory/ilAssLacExpressionManufacturer.php';
        include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Factory/ilAssLacOperationManufacturer.php';
        include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacCompositeBuilder.php";
    }

    /**
     * Parses the delivered condition and creates a composite tree Structure
     *
     * @param $condition
     *
     * @see CompositeBuilder::create()
     * @return array
     */
    public function parse(string $condition): array
    {
        $this->condition = $condition;
        $this->checkBrackets();
        $this->fetchExpressions();
        $this->fetchOperators();
        $this->cannonicalizeCondition();
        $nodes = $this->createNodeArray();
        $compositeBuilder = new ilAssLacCompositeBuilder();
        return $compositeBuilder->create($nodes);
    }

    /**
     * Matches all expressions in the current condition and assign these to the class attribute ConditionParser::$expressions
     *
     * @see AbstractManufacturer::match()
     * @see ExpressionManufacturer::getPattern()
     * @see Parser::$expressions
     */
    protected function fetchExpressions(): void
    {
        $manufacturer = ilAssLacExpressionManufacturer::_getInstance();
        $this->expressions = $manufacturer->match($this->condition);
    }

    /**
     * Matches all operators in the current condition and assign these to the class attribute ConditionParser::$operators
     *
     * @see AbstractManufacturer::match()
     * @see OperationManufacturer::getPattern()
     * @see Parser::$operators
     */
    protected function fetchOperators(): void
    {
        $manufacturer = ilAssLacOperationManufacturer::_getInstance();
        $this->operators = $manufacturer->match($this->condition);
    }

    /**
     * Canonicalize the condition into a more general form. <br />
     * It replaces all expression with "n" and all operators with "o" <br />
     * so that the result of a condition after canonicalization could be:<br />
     * <br />
     * (n o n) o (n o n) o n
     */
    protected function cannonicalizeCondition(): void
    {
        $manufacturer = ilAssLacExpressionManufacturer::_getInstance();
        $this->condition = preg_replace($manufacturer->getPattern(), 'n', $this->condition);
        $manufacturer = ilAssLacOperationManufacturer::_getInstance();
        $this->condition = preg_replace($manufacturer->getPattern(), 'o', $this->condition);
        $this->condition = preg_replace("/no/", "n o", $this->condition);
        $this->condition = preg_replace("/on/", "o n", $this->condition);

        for ($i = 0; $i < strlen($this->condition); $i++) {
            if ($this->condition[$i] == "!" && !$this->isNegationSurroundedByBrackets($i)) {
                $this->surroundNegationExpression($i);
            }
        }
    }

    protected function checkBrackets(): void
    {
        $level = 0;
        for ($i = 0; $i < strlen($this->condition); $i++) {
            switch ($this->condition[$i]) {
                case '(':
                    $level++;
                    break;
                case ')':
                    $level--;
                    if ($level < 0) {
                        // An opening bracket is missing _before_ the current
                        // position, but not necessarily in the entire
                        // expression: it is possible that just the order of
                        // brackets is wrong, like in "R>=%75%)(".

                        $num_open = substr_count($this->condition, "(", $i);
                        $num_close = substr_count($this->condition, ")", $i);

                        if ($num_open < $num_close) {
                            throw new ilAssLacMissingBracket("(");
                        // Could check for missing closing brackets here as
                        // well, but this is unnecessary: just report the first
                        // problem.
                        //} else if ($num_open > $num_close) {
                        //    throw new ilAssLacMissingBracket(")");
                        } else {
                            throw new ilAssLacBracketOrder($i + 1);
                                    // start counting offset with 1, for humans
                        }
                    }
                    break;
            }
        }
        if ($level > 0) {
            throw new ilAssLacMissingBracket(")");
        }
    }

    /**
     * Creates an array representing all Nodes in a condition based on the fetched expressions and operators.<br />
     * The array has a tree representation; its depth corresponds to the bracketing in the condition.<br />
     * The array contains four main keys to identify the elements:<br />
     * <br />
     * <table>
     * <tr>
     *     <th>Key</th><th>Values</th><th>Description</th>
     * </tr>
     * <tr>
     *     <td>type</td>
     *     <td>"group", "expression", "operator"</td>
     *     <td>The type of the node - Group is used to introduce the next tree
     *     depth. The type of the outermost structure returned when calling this
     *     function from the outside is always group; that group contains the
     *     parsed expression.</td>
     * </tr>
     * <tr>
     *     <td>negated</td>
     *     <td>boolean</td>
     *     <td>Whether this condition is logically inverted or not</td>
     * </tr>
     * <tr>
     *     <td>value</td>
     *     <td>mixed</td>
     *     <td>Contains an extracted expression or operation from a
     *     condition</td>
     * </tr>
     * <tr>
     *     <td>nodes</td>
     *     <td>array</td>
     *     <td>Contains a node array</td>
     * </tr>
     * </table>
     * <p>
     * This function should only be called if bracket nesting in the input
     * condition is known to be valid. In other cases, its output may be
     * different than described above.
     *
     * @return array
     *     If called from the outside and if bracket nesting in the input is
     *     correct, a structure in form of an array with certain keys, as
     *     explained above.
     *     <p>
     *     If called recursively during parsing, a plain node array to be put
     *     into ['nodes'] of an array as described above.
     */
    protected function createNodeArray(): array
    {
        $expected = array("n", "(", "!");
        $group = array();
        $negation = false;

        while ($this->index < strlen($this->condition)) {
            // current character:
            $a = $this->condition[$this->index];

            if (trim($a) == "") {
                $this->spaces++;
            } elseif (in_array($a, $expected)) {
                if ($a == ')') {
                    return $group;
                } elseif ($a == 'n') {
                    $group[] = array('type' => 'expression', 'value' => array_shift($this->expressions));
                    $expected = array("o", ")");
                } elseif ($a == 'o') {
                    $group[] = array('type' => 'operator', 'value' => array_shift($this->operators));
                    $expected = array("n", "(", "!");
                } elseif ($a == '(') {
                    $this->index++;
                    $elements = $this->createNodeArray();
                    $group[] = array('type' => "group", "negated" => $negation, 'nodes' => $elements);
                    $negation = false;
                    $expected = array("o", ")");
                } elseif ($a == '!') {
                    $negation = true;
                }
            } else {
                throw new ilAssLacConditionParserException($this->index - $this->spaces + 1);
            }

            $this->index++;
        }
        return array('type' => 'group', "negated" => $negation, 'nodes' => $group);
    }

    /**
     * @return array
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    /**
     * @param int $index
     */
    protected function surroundNegationExpression(int $index): void
    {
        $start = strpos($this->condition, "n", $index + 1);
        $end = false;

        if ($start !== false) {
            $end = strpos($this->condition, "n", $start + 1);
        }

        if ($start !== false && $end !== false) {
            $this->condition = substr_replace($this->condition, "(n o n)", $start, $end - $start + 1);
        }
    }

    /**
     * @param int $index
     *
     * @return boolean
     */
    protected function isNegationSurroundedByBrackets(int $index)
    {
        $next_bracket = strpos($this->condition, "(", $index + 1);
        $next_expression = strpos($this->condition, "n", $index + 1);

        return $next_bracket !== false & $next_bracket < $next_expression;
    }
}
