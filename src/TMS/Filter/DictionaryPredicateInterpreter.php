<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter;

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
	public function interpret( \ILIAS\TMS\Filter\Predicates\Predicate $p, array $d) {
		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateTrue) {
			return true;
		}

		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateNot) {
			return !$this->interpret($p->sub(), $d);
		}

		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateAny) {
			$predicates = $p->subs();
			foreach ($predicates as $predicate) {
				if( $this->interpret($predicate, $d) ) {
					return true;
				}
			}
			return false;
		}

		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateAll) {
			$predicates = $p->subs();
			foreach ($predicates as $predicate) {
				if( !$this->interpret($predicate, $d) ) {
					return false;
				}
			}
			return true;
		}

		if($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateIn) {
			$value = $p->getValue();
			$list = $p->getList()->values();
			foreach($list as $list_el) {
				if($this->interpret($value->EQ($list_el),$d)) {
					return true;
				}
			}
			return false;
		}

		if($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateIsNull) {
			if($field = current($p->fields())) {
				$field_name = $field->name();
				if(array_key_exists($field_name, $d)) {
					return $d[$field_name] === null;
				}
				throw new \InvalidArgumentException("DictionaryPredicateInterpreter::interpret :"
					." no field with name $field_name");
			}
			return false;
		}


		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateComparison) {
			$left = $p->left();
			$right = $p->right();
			if($left instanceof Predicates\Field) {
				$left_name = $left->name();
				$left = array_key_exists($left_name, $d) ? $d[$left_name] : null;
				$left_type = $this->fieldType($left);
			} else {
				$left_type = $this->varType($left);
				$left = $left->value();
			}
			if($right instanceof \ILIAS\TMS\Filter\Predicates\Field) {
				$right_name = $right->name();
				$right = array_key_exists($right_name, $d) ? $d[$right_name] : null;
				$right_type = $this->fieldType($right);
			} else {
				$right_type = $this->varType($right);
				$right = $right->value();
			}
			if($right_type !== $left_type) {
				throw new \InvalidArgumentException("DictionaryPredicateInterpreter::interpret :"
					." comparing different field types");
			}
			if($p instanceof  \ILIAS\TMS\Filter\Predicates\PredicateEq) {
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
			if($p instanceof  \ILIAS\TMS\Filter\Predicates\PredicateNeq) {
				if( $right_type === self::IS_DATE ) {
					return $right != $left ;
				}
				if( $right_type === self::IS_STR ) {
					return strcmp($left, $right) !== 0 ? true : false;
				}
				if( $right_type === self::IS_INT ) {
					return $left !== $right;
				}
			}
			if($p instanceof  \ILIAS\TMS\Filter\Predicates\PredicateLt) {
				if( $right_type === self::IS_DATE ) {
					return $left < $right;
				}
				if( $right_type === self::IS_STR ) {
					return strcmp($left, $right) < 0 ? true : false;
				}
				if( $right_type === self::IS_INT ) {
					return $left < $right;
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
		throw new \InvalidArgumentException("DictionaryPredicateInterpreter::fieldType : invalid field type");
	}

	protected function varType($var) {
		if( $var instanceof \ILIAS\TMS\Filter\Predicates\ValueStr) {
			return self::IS_STR;
		}
		if( $var instanceof \ILIAS\TMS\Filter\Predicates\ValueInt) {
			return self::IS_INT;
		}
		if( $var instanceof \ILIAS\TMS\Filter\Predicates\ValueDate) {
			return self::IS_DATE;
		}
		throw new \InvalidArgumentException("DictionaryPredicateInterpreter::varType : invalid var type");
	}
}
