<?php

require_once "./Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php";

/**
 * Class ilOperatorsExpressionMapping
 *
 * Date: 03.12.13
 * Time: 13:16
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
abstract class ilOperatorsExpressionMapping {

	/**
	 * @var array
	 */
	private static $mappings = array(
		iQuestionCondition::PercentageResultExpression => array("<", "<=", "=", ">=", ">", "<>"),
		iQuestionCondition::NumericResultExpression => array("<", "<=", "=", ">=", ">", "<>"),
		iQuestionCondition::StringResultExpression => array("=", "<>"),
		iQuestionCondition::MatchingResultExpression => array("=", "<>"),
		iQuestionCondition::OrderingResultExpression => array("=", "<>"),
		iQuestionCondition::NumberOfResultExpression => array("=", "<>"),
		iQuestionCondition::ExclusiveResultExpression => array("=", "<>"),
		iQuestionCondition::EmptyAnswerExpression => array("=", "<>")
	);

	/**
	 * @param string $expression
	 *
	 * @return array
	 */
	public static function getOperatorsByExpression($expression)
	{
		return self::$mappings[$expression];
	}

	public static function getAll()
	{
		return self::$mappings;
	}
}
 