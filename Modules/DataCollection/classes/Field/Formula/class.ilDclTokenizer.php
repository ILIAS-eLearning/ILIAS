<?php

/**
 * Class ilDclTokenizer
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclTokenizer {

	/**
	 * Split expression by & (ignore escaped &-symbols with backslash)
	 *
	 * @param string $expression Global expression to parse
	 *
	 * @return array
	 */
	public static function getTokens($expression) {
		$expression = ltrim($expression, '=');
		$expression = trim($expression);
		$tokens = preg_split('#[^\\\\]&#', $expression);

		return array_map('trim', $tokens);
	}


	/**
	 * Generate tokens for a math expression
	 *
	 * @param string $math_expression Expression of type math
	 *
	 * @return array
	 */
	public static function getMathTokens($math_expression) {
		$operators = array_keys(ilDclExpressionParser::getOperators());
		$pattern = '#((^\[\[)[\d\.]+)|(\(|\)|\\' . implode("|\\", $operators) . ')#';
		$tokens = preg_split($pattern, $math_expression, - 1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		return array_map('trim', $tokens);
	}
}

?>
