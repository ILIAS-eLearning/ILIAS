<?php

/* Copyright (c) 2016 Denis Köpfer, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter;

/**
 * Interpreter to check the predicate on a dictionary.
 */
class SqlPredicateInterpreter {

	const IS_STR = 1;
	const IS_INT = 2;
	const IS_DATE = 3;
	const IS_FIELD = 4;

	protected $db;

	public function __construct(\ilDBInterface $db) {
		$this->db = $db;
	}

	/**
	 * Check a predicate on a dictonary.
	 * 
	 * @return	bool
	 */
	public function interpret( \ILIAS\TMS\Filter\Predicates\Predicate $p) {
		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateTrue) {
			return 'TRUE ';
		}

		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateNot) {
			return 'NOT ('.$this->interpret($p->sub()).') ';
		}

		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateAny) {
			$parts = array();
			$predicates = $p->subs();
			foreach ($predicates as $predicate) {
				$parts[] = '('.$this->interpret($predicate).') ';
			}

			return implode('OR ',$parts);
		}

		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateAll) {
			$parts = array();
			$predicates = $p->subs();
			foreach ($predicates as $predicate) {
				$parts[] = '('.$this->interpret($predicate).') ';
			}

			return implode('AND ',$parts);
		}

		if($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateIn) {
			$value = $p->getValue();
			$return .= $this->quoteFieldOrValue($value).' IN(';
			$list = $p->getList()->values();
			$in = array();
			foreach($list as $list_el) {
				$in[] = $this->quoteFieldOrValue($list_el);
			}
			return $return.implode(',',$in).') ';
		}

		if($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateIsNull) {
			if($field = current($p->fields())) {
				return $this->quoteFieldOrValue($field)." IS NULL ";
			}
			return "FALSE ";
		}

		if ($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateComparison) {
			$left = $p->left();
			$right = $p->right();
			$left_type = $this->varType($left);
			$left_quote = $this->quoteFieldOrValue($left);
			$right_type = $this->varType($right);
			$right_quote = $this->quoteFieldOrValue($right);

			if($right_type !== $left_type && $right_type !== self::IS_FIELD && $left_type !== self::IS_FIELD  ) {
				throw new \InvalidArgumentException("SqlPredicateInterpreter::interpret :"
					." comparing different field types");
			}
			if($p instanceof  \ILIAS\TMS\Filter\Predicates\PredicateEq) {
				return $left_quote.' = '.$right_quote.' ';
			}
			if($p instanceof  \ILIAS\TMS\Filter\Predicates\PredicateNeq) {
				return $left_quote.' != '.$right_quote.' ';
			}
			if($p instanceof  \ILIAS\TMS\Filter\Predicates\PredicateLt) {
				return $left_quote.' < '.$right_quote.' ';
			}
			if($p instanceof \ILIAS\TMS\Filter\Predicates\PredicateLike) {
				return $left_quote.' LIKE '.$right_quote;
			}
		}
		throw new \InvalidArgumentException("SqlPredicateInterpreter::interpret : possibly unknown prediacte type");
	}

	protected function quoteFieldOrValue($var) {

		if( $var instanceof \ILIAS\TMS\Filter\Predicates\ValueStr) {
			return $this->db->quote($var->value(),'text');
		}
		if( $var instanceof \ILIAS\TMS\Filter\Predicates\ValueInt) {
			return $this->db->quote($var->value(),'integer');
		}
		if( $var instanceof \ILIAS\TMS\Filter\Predicates\ValueDate) {
			return $this->db->quote($var->value()->format('Y-m-d'),'date');
		}
		if( $var instanceof \ILIAS\TMS\Filter\Predicates\Field) {
			return $this->quoteField($var);
		}
		throw new \InvalidArgumentException("SqlPredicateInterpreter::varType : invalid var type");
	}

	protected function quoteField(\ILIAS\TMS\Filter\Predicates\Field $field) {
		$field_name = $field->name();
		if(0 === preg_match('#^([a-zA-Z0-9_]+.)?[a-zA-Z0-9_]+$#', $field_name)) {
			throw new \InvalidArgumentException("SqlPredicateInterpreter::quoteField : field title invalid");
		}
		$field_name_parts = explode('.',$field_name);
		array_walk($field_name_parts, function (&$name_part) { $name_part = '`'.$name_part.'`';} );
		return implode('.',$field_name_parts);
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
			if( $var instanceof \ILIAS\TMS\Filter\Predicates\Field) {
			return self::IS_FIELD;
		}
		throw new \InvalidArgumentException("SqlPredicateInterpreter::varType : invalid var type");
	}
}
