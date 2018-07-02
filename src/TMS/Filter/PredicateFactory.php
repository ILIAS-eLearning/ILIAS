<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter;

/**
 * Factory to build predicates.
 *
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
class PredicateFactory {
	// BASIC PREDICATES

	/**
	 * A predicate that always matches.
	 *
	 * @return	Predicate
	 */
	public function _TRUE() {
		return new Predicates\PredicateTrue($this);
	}

	/**
	 * A predicate that never matches.
	 *
	 * @return	Predicate
	 */
	public function _FALSE() {
		return $this->_NOT()->_TRUE();
	}

	// ATOMS FOR BUILDING PREDICATES

	/**
	 * An integer value.
	 *
	 * @param	int		$i
	 * @throws	\InvalidArgumentException
	 * @return	Value
	 */
	public function int($i) {
		return new Predicates\ValueInt($this, $i);
	}

	/**
	 * An string value.
	 *
	 * @param	str		$s
	 * @throws	\InvalidArgumentException
	 * @return	Value
	 */
	public function str($s) {
		return new Predicates\ValueStr($this, $s);
	}

	/**
	 * An date value.
	 *
	 * @param	str|\DateTime		$dt
	 * @throws	\InvalidArgumentException
	 * @return	Value
	 */
	public function date($dt) {
		return new Predicates\ValueDate($this, $dt);
	}

	/**
	 * Get a reference to a field.
	 *
	 * @param	str		$name
	 * @return	Field
	 */
	public function field($name) {
		return new Predicates\Field($this, $name);
	}

	/**
	 * Construct a list from int values.
	 *
	 * @param	string[]|int[]	$elements
	 * @return	ValueList
	 */
	public function list_int(/*...$elements*/) {
		$elements = func_get_args();
		$val_objs = array();
		foreach ($elements as $el) {
			$val_objs[] = $this->int($el);
		}
		return new Predicates\ValueList($val_objs);
	}

	/**
	 * Construct a list from array of int values.
	 *
	 * @param	string[]|int[]	$ints
	 * @return	ValueList
	 */
	public function list_int_by_array(array $ints) {
		$val_objs = array();
		foreach ($ints as $int) {
			$val_objs[] = $this->int($int);
		}
		return new Predicates\ValueList($val_objs);
	}

	/**
	 * Construct a list from array of str values.
	 *
	 * @param	string[]	$strs
	 * @return	ValueList
	 */
	public function list_string_by_array(array $strs) {
		$val_objs = array();
		foreach ($strs as $str) {
			$val_objs[] = $this->str($str);
		}
		return new Predicates\ValueList($val_objs);
	}

	/**
	 * Construct a list from array of int values.
	 *
	 * @param	Field[]	$fields
	 * @return	ValueList
	 */
	public function list_field_by_array(array $fields) {
		$val_objs = array();
		foreach ($fields as $field) {
			if(!$field instanceof Predicates\Field) {
				throw new \InvalidArgumentException('arguments must be of type Predicates\Field');
			}
			$val_objs[] = $field;
		}
		return new Predicates\ValueList($val_objs);
	}


	/**
	 * Construct a list from str values.
	 *
	 * @param	string[]|int[]	$elements
	 * @return	ValueList
	 */
	public function list_str(/*...$elements*/) {
		$elements = func_get_args();
		$val_objs = array();
		foreach ($elements as $el) {
			$val_objs[] = $this->str($el);
		}
		return new Predicates\ValueList($val_objs);
	}

	/**
	 * A predicate that is true if l equals r.
	 *
	 * @param	ValueLike		$l
	 * @param	ValueLike		$r
	 * @return	Predicate
	 */
	public function EQ(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		return new Predicates\PredicateEq($this, $l, $r);
	}

	public function NEQ(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		return new Predicates\PredicateNeq($this, $l, $r);
	}

	/**
	 * A predicate that is true if l is lower or equals r.
	 *
	 * @param	ValueLike		$l
	 * @param	ValueLike		$r
	 * @return	Predicate
	 */
	public function LT(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		return new Predicates\PredicateLt($this, $l, $r);
	}

	public function LE(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		$lt = new Predicates\PredicateLt($this, $l, $r);
		$eq = new Predicates\PredicateEq($this, $l, $r);
		return $this->_ANY($lt,$eq);
	}

	public function GE(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		return $this->LE($r, $l);
	}

	public function GT(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		return $this->LT($r, $l);
	}

	/**
	 * A predicate that is true when the value is part of a list.
	 *
	 * @param	ValueLike		$l
	 * @param	ValueList		$r
	 * @return	Predicate
	 */
	public function IN(Predicates\ValueLike $l, Predicates\ValueList $r) {
		return new Predicates\PredicateIn($this, $l, $r);
	}

	// COMBINATORS

	/**
	 * A predicate that matches iff all of the subpredicates matches.
	 *
	 * @param	Predicate[]	...
	 * @return	Predicate
	 */
	public function _ALL(/* ...$subs*/) {
		$subs = func_get_args();
		return new Predicates\PredicateAll($this, $subs);
	}

	/**
	 * A predicate that matches iff both subpredicates match.
	 *
	 * @param	Predicate	$l
	 * @param	Predicate	$r
	 * @return	Predicate
	 */
	public function _AND(Predicates\Predicate $l, Predicates\Predicate $r) {
		return $this->_ALL($l, $r);
	}

	/**
	 * A predicate that matches iff any of the subpredicates matches.
	 *
	 * @param	Predicate[]	...
	 * @return	Predicate
	 */
	public function _ANY(/* ...$subs */) {
		$subs = func_get_args();
		return new Predicates\PredicateAny($this, $subs);
	}

	/**
	 * A predicate that matches iff the one or the other subpredicate matches.
	 *
	 * @param	Predicate	$l
	 * @param	Predicate	$r
	 * @return	Predicate
	 */
	public function _OR(Predicates\Predicate $l, Predicates\Predicate $r) {
		return $this->_ANY($l, $r);
	}

	/**
	 * A predicate that matches iff the  subpredicate does not match.
	 *
	 * @param	Predicate|null	$o
	 * @return	Predicate
	 */
	public function _NOT(Predicates\Predicate $o = null) {
		if ($o !== null) {
			# TODO: We could use NOT NOT a = a
			return new Predicates\PredicateNot($this, $o);
		}

		$self = $this;
		return new Predicates\FluentPredicateFactory(function (Predicates\Predicate $o) use ($self) {
			return $self->_NOT($o);
		}, $this);
	}

	
	public function IS_NULL(Predicates\ValueLike $val) {
		return new Predicates\PredicateIsNull($this, $val);
	}

	public function LIKE(Predicates\ValueLike $left, Predicates\ValueLike $right) {
		return new Predicates\PredicateLike($this,$left,$right);
	}
}
