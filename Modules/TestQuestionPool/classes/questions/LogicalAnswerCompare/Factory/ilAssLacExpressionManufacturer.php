<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Factory/ilAssLacAbstractManufacturer.php";

/**
 * Class ExpressionManufacturer
 *
 * Date: 25.03.13
 * Time: 15:12
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacExpressionManufacturer extends ilAssLacAbstractManufacturer
{

    /**
     * A Singleton Instance of the ExpressionManufacturer
     *
     * @see ExpressionManufacturer::_getInstance()
     * @see ExpressionManufacturer::__construct()
     *
     * @var null|ilAssLacExpressionManufacturer
     */
    protected static $instance = null;

    /**
     * Get an Instance of ExpressionManufacturer
     *
     * @return ilAssLacExpressionManufacturer
     */
    public static function _getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ilAssLacExpressionManufacturer();
        }
        return self::$instance;
    }

    /**
     * /**
     * Create a new specific Composite object which is representing the delivered Attribute
     *
     * @param string $attribute
     *
     * @return ilAssLacAbstractComposite|ilAssLacAnswerOfQuestionExpression|ilAssLacAnswerOfCurrentQuestionExpression|ilAssLacNumberOfResultExpression|ilAssLacNumericResultExpression|ilAssLacPercentageResultExpression|ilAssLacResultOfAnswerOfQuestionExpression|ilAssLacResultOfAnswerOfCurrentQuestionExpression|ilAssLacStringResultExpression
     * @throws ilAssLacUnsupportedExpression
     */
    public function manufacture($attribute)
    {
        $expression = null;

        switch (true) {
            case preg_match(ilAssLacResultOfAnswerOfQuestionExpression::$pattern, $attribute):
                $expression = new ilAssLacResultOfAnswerOfQuestionExpression();
                break;
            case preg_match(ilAssLacResultOfAnswerOfCurrentQuestionExpression::$pattern, $attribute):
                $expression = new ilAssLacResultOfAnswerOfCurrentQuestionExpression();
                break;
            case preg_match(ilAssLacAnswerOfQuestionExpression::$pattern, $attribute):
                $expression = new ilAssLacAnswerOfQuestionExpression();
                break;
            case preg_match(ilAssLacAnswerOfCurrentQuestionExpression::$pattern, $attribute):
                $expression = new ilAssLacAnswerOfCurrentQuestionExpression();
                break;
            case preg_match(ilAssLacPercentageResultExpression::$pattern, $attribute):
                $expression = new ilAssLacPercentageResultExpression();
                break;
            case preg_match(ilAssLacNumberOfResultExpression::$pattern, $attribute):
                $expression = new ilAssLacNumberOfResultExpression();
                break;
            case preg_match(ilAssLacNumericResultExpression::$pattern, $attribute):
                $expression = new ilAssLacNumericResultExpression();
                break;
            case preg_match(ilAssLacStringResultExpression::$pattern, $attribute):
                $expression = new ilAssLacStringResultExpression();
                break;
            case preg_match(ilAssLacMatchingResultExpression::$pattern, $attribute):
                $expression = new ilAssLacMatchingResultExpression();
                break;
            case preg_match(ilAssLacOrderingResultExpression::$pattern, $attribute):
                $expression = new ilAssLacOrderingResultExpression();
                break;
            case preg_match(ilAssLacExclusiveResultExpression::$pattern, $attribute):
                $expression = new ilAssLacExclusiveResultExpression();
                break;
            case preg_match(ilAssLacEmptyAnswerExpression::$pattern, $attribute):
                $expression = new ilAssLacEmptyAnswerExpression();
                break;
            default:
                throw new ilAssLacUnsupportedExpression($attribute);
                break;
        }

        $expression->parseValue($attribute);
        return $expression;
    }

    /**
     * This function create a regular expression to match all expression in a condition. <br />
     * The following string is created by this function <b>'/%[0-9]+%|#[0-9]+#|\+[0-9]+\+|Q[0-9]+([^\[|0-9]|$)|Q[0-9]+\[[0-9]+\]|~.*?~'</b><br />
     * It matches all expression in a condition and is divided into the following parts:
     *
     * <pre>
     * Qn        /Q[0-9]+(?!\\[)/
     * Qn[m]     /Q[0-9]+\\[[0-9]+\\]/
     * %n%       /%[0-9]+%/
     * +n+       /\\+[0-9]+\\+/
     * #n#       /#[0-9]+#/
     * ~TEXT~    /~.*?~/				Hier gibt es noch Probleme, wenn im Text ein ~ enthalten ist
     * </pre>
     *
     * @return string
     */
    public function getPattern()
    {
        return
            "/" .
            substr(ilAssLacPercentageResultExpression::$pattern, 1, strlen(ilAssLacPercentageResultExpression::$pattern) - 2) . "|" .
            substr(ilAssLacNumericResultExpression::$pattern, 1, strlen(ilAssLacNumericResultExpression::$pattern) - 2) . "|" .
            substr(ilAssLacNumberOfResultExpression::$pattern, 1, strlen(ilAssLacNumberOfResultExpression::$pattern) - 2) . "|" .
            substr(ilAssLacAnswerOfQuestionExpression::$pattern, 1, strlen(ilAssLacAnswerOfQuestionExpression::$pattern) - 2) . "|" .
            substr(ilAssLacAnswerOfCurrentQuestionExpression::$pattern, 1, strlen(ilAssLacAnswerOfCurrentQuestionExpression::$pattern) - 2) . "|" .
            substr(ilAssLacResultOfAnswerOfQuestionExpression::$pattern, 1, strlen(ilAssLacResultOfAnswerOfQuestionExpression::$pattern) - 2) . "|" .
            substr(ilAssLacResultOfAnswerOfCurrentQuestionExpression::$pattern, 1, strlen(ilAssLacResultOfAnswerOfCurrentQuestionExpression::$pattern) - 2) . "|" .
            substr(ilAssLacStringResultExpression::$pattern, 1, strlen(ilAssLacStringResultExpression::$pattern) - 2) . "|" .
            substr(ilAssLacMatchingResultExpression::$pattern, 1, strlen(ilAssLacMatchingResultExpression::$pattern) - 2) . "|" .
            substr(ilAssLacOrderingResultExpression::$pattern, 1, strlen(ilAssLacOrderingResultExpression::$pattern) - 2) . "|" .
            substr(ilAssLacExclusiveResultExpression::$pattern, 1, strlen(ilAssLacExclusiveResultExpression::$pattern) - 2) . "|" .
            substr(ilAssLacEmptyAnswerExpression::$pattern, 1, strlen(ilAssLacEmptyAnswerExpression::$pattern) - 2) .
            "/";
    }

    /**
     * Private constructor to prevent creating of an object of ExpressionManufacturer
     */
    private function __construct()
    {
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAnswerOfQuestionExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAnswerOfCurrentQuestionExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacResultOfAnswerOfQuestionExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacResultOfAnswerOfCurrentQuestionExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacPercentageResultExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacNumberOfResultExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacNumericResultExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacStringResultExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacMatchingResultExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacOrderingResultExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacExclusiveResultExpression.php";
        require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacEmptyAnswerExpression.php";
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacUnsupportedExpression.php';
    }

    /**
     * Private clone to prevent cloning an object of ExpressionManufacturer
     */
    private function __clone()
    {
    }
}
