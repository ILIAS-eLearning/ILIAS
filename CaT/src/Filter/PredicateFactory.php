<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

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
	 * @return	ilPredicate
	 */
	public function _TRUE() {
		return new Predicates\PredicateTrue($this);
	}

	/**
	 * A predicate that never matches.
	 *
	 * @return	ilPredicate
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
	 * @return	ilValue
	 */
	public function int($i) {
		return new Predicates\ValueInt($this, $i);
	}

	/**
	 * An string value.
	 *
	 * @param	str		$s
	 * @throws	\InvalidArgumentException
	 * @return	ilValue
	 */
	public function str($s) {
		return new Predicates\ValueStr($this, $s);
	}

	/**
	 * An date value.
	 *
	 * @param	str|\DateTime		$dt
	 * @throws	\InvalidArgumentException
	 * @return	ilValue
	 */
	public function date($dt) {
		return new Predicates\ValueDate($this, $d);
	}

	/**
	 * Get a reference to a field.
	 *
	 * @param	str		$name
	 * @return	ilField
	 */
	public function field($name) {
		return new Predicates\Field($this, $name);
	}

	/**
	 * A predicate that is true if l equals r.
	 *
	 * @param	ilValueLike		$l
	 * @param	ilValueLike		$r
	 * @return	ilPredicate
	 */
	public function EQ(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		return new Predicates\PredicateEq($this, $l, $r);
	}

	/**
	 * A predicate that is true if l is lower or equals r.
	 *
	 * @param	ilValueLike		$l
	 * @param	ilValueLike		$r
	 * @return	ilPredicate
	 */
	public function LE(Predicates\ValueLike $l, Predicates\ValueLike $r) {
		return new Predicates\PredicateLe($this, $l, $r);
	}

	// TODO:
	//		- NEQ
	//		- LT
	//		- GE
	//		- GT

	// COMBINATORS

	/**
	 * A predicate that matches iff all of the subpredicates matches.
	 *
	 * @param	ilPredicate[]	...
	 * @return	ilPredicate
	 */
	public function _ALL(/* ...$subs*/) {
		$subs = func_get_args();
		return new Predicates\PredicateAll($this, $subs);
	}

	/**
	 * A predicate that matches iff both subpredicates match.
	 *
	 * @param	ilPredicate	$l
	 * @param	ilPredicate	$r
	 * @return	ilPredicate
	 */
	public function _AND(Predicates\Predicate $l, Predicates\Predicate $r) {
		return $this->_ALL($l, $r);
	}

	/**
	 * A predicate that matches iff any of the subpredicates matches.
	 *
	 * @param	ilPredicate[]	...
	 * @return	ilPredicate
	 */
	public function _ANY(/* ...$subs */) {
		$subs = func_get_args();
		return new Predicates\PredicateAny($this, $subs);
	}

	/**
	 * A predicate that matches iff the one or the other subpredicate matches.
	 *
	 * @param	ilPredicate	$l
	 * @param	ilPredicate	$r
	 * @return	ilPredicate
	 */
	public function _OR(Predicates\Predicate $l, Predicates\Predicate $r) {
		return $this->_ANY($l, $r);
	}

	/**
	 * A predicate that matches iff the  subpredicate does not match.
	 *
	 * @param	ilPredicate|null	$o
	 * @return	ilPredicate
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
}