<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
 * Interpreter to check the predicate on a dictionary.
 */
class DictionaryPredicateInterpreter {

	const IS_STR = 1;
	const IS_INT = 2;
	const IS_DATE = 3;
	/**
	 * Check a predicate on a dictonary.
	 * 
	 * @return	bool
	 */
	public function interpret( \CaT\Filter\Predicates\Predicate $p, array $d) {
		if ($p instanceof \CaT\Filter\Predicates\PredicateTrue) {
			return true;
		}

		if ($p instanceof \CaT\Filter\Predicates\PredicateNot) {
			return !$this->interpret($p->sub(), $d);
		}

		if ($p instanceof \CaT\Filter\Predicates\PredicateAny) {
			$predicates = $p->subs();
			foreach ($predicates as $predicate) {
				if( $this->interpret($predicate, $d) ) {
					return true;
				}
			}
			return false;
		}

		if ($p instanceof \CaT\Filter\Predicates\PredicateAll) {
			$predicates = $p->subs();
			foreach ($predicates as $predicate) {
				if( !$this->interpret($predicate, $d) ) {
					return false;
				}
			}
			return true;
		}

		if ($p instanceof \CaT\Filter\Predicates\PredicateComparison) {
			$left = $p->left;
			$right = $p->right;
			if($left instanceof Predicates\Field) {
				$left_name = $left->name();
				if(!isset($d[$left_name])){
					return false;
				}
				$left = $d[$left_name];
				$left_type = $this->fieldType($left);
			} else {
				$left_type = $this->varType($left);
				$left = $left->value();
			}
			if($right instanceof \CaT\Filter\Predicates\Field) {
				$right_name = $right->name();
				if(!isset($d[$right_name])){
					return false;
				}
				$right = $d[$right_name];
				$right_type = $this->fieldType($right);
			} else {
				$right_type = $this->varType($right);
				$right = $right->value();
			}
			if(!$right_type || !$left_type || $right_type !== $left_type) {
				return false;
			}
			if($p instanceof  \CaT\Filter\Predicates\PredicateEq) {
				if( $right_type === self::IS_DATE ) {
					return $right == $left;
				}
				if( $right_type === self::IS_STR ) {
					return strcmp($left, $right) === 0 ? true : false;
				}
				if( $right_type === self::IS_INT ) {
					return $left === $right;
				}
			}
			if($p instanceof  \CaT\Filter\Predicates\PredicateLe) {
				if( $right_type === self::IS_DATE ) {
					return $right <= $left;
				}
				if( $right_type === self::IS_STR ) {
					return strcmp($left, $right) <= 0 ? true : false;
				}
				if( $right_type === self::IS_INT ) {
					return $left <= $right;
				}
			}
		}
		return null;
	}

	protected function fieldType($var) {
		if(is_string($var)) {
			return self::IS_STR;
		}
		if(is_int($var)) {
			return self::IS_INT;
		}
		if(get_class($var) === 'DateTime') {
			return self::IS_DATE;
		}
		return false;
	}

	protected function varType($var) {
		if( $var instanceof \CaT\Filter\Predicates\ValueStr) {
			return self::IS_STR;
		}
		if( $var instanceof \CaT\Filter\Predicates\ValueInt) {
			return self::IS_INT;
		}
		if( $var instanceof \CaT\Filter\Predicates\ValueDate) {
			return self::IS_DATE;
		}
		return false;
	}
}