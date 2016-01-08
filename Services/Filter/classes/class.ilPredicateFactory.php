<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

/**
 * Factory to build predicates.
 *
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
class ilPredicateFactory {
	// BASIC PREDICATES

	/**
	 * A predicate that always matches.
	 *
	 * @return	ilPredicate
	 */
	public function _TRUE() {
		require_once("Services/Filter/classes/Predicates/class.ilPredicateTrue.php");
		return new ilPredicateTrue($this);
	}

	/**
	 * A predicate that never matches.
	 *
	 * @return	ilPredicate
	 */
	public function _FALSE() {
		return $this->_NOT()->_TRUE();
	}

	// ATOMS FOR PREDICATES

	// COMBINATORS

	/**
	 * A predicate that matches iff all of the subpredicates matches.
	 *
	 * @param	ilPredicate[]	...
	 * @return	ilPredicate
	 */
	public function _ALL(/* ...$subs*/) {
		require_once("Services/Filter/classes/Predicates/class.ilPredicateAll.php");
		$subs = func_get_args();
		return new ilPredicateAll($this, $subs);
	}

	/**
	 * A predicate that matches iff both subpredicates match.
	 *
	 * @param	ilPredicate	$l
	 * @param	ilPredicate	$r
	 * @return	ilPredicate
	 */
	public function _AND(ilPredicate $l, ilPredicate $r) {
		return $this->_ALL($l, $r);
	}

	/**
	 * A predicate that matches iff any of the subpredicates matches.
	 *
	 * @param	ilPredicate[]	...
	 * @return	ilPredicate
	 */
	public function _ANY(/* ...$subs */) {
		require_once("Services/Filter/classes/Predicates/class.ilPredicateAny.php");
		$subs = func_get_args();
		return new ilPredicateAny($this, $subs);
	}

	/**
	 * A predicate that matches iff the one or the other subpredicate matches.
	 *
	 * @param	ilPredicate	$l
	 * @param	ilPredicate	$r
	 * @return	ilPredicate
	 */
	public function _OR(ilPredicate $l, ilPredicate $r) {
		return $this->_ANY($l, $r);
	}

	/**
	 * A predicate that matches iff the  subpredicate does not match.
	 *
	 * @param	ilPredicate|null	$o
	 * @return	ilPredicate
	 */
	public function _NOT(ilPredicate $o = null) {
		if ($o !== null) {
			# TODO: We could use NOT NOT a = a
			require_once("Services/Filter/classes/Predicates/class.ilPredicateNot.php");
			return new ilPredicateNot($this, $o);
		}

		require_once("Services/Filter/classes/Predicates/class.ilFluentPredicateFactory.php");
		$self = $this;
		return new ilFluentPredicateFactory(function (ilPredicate $o) use ($self) {
			return $self->_NOT($o);
		}, $this);
	}

}