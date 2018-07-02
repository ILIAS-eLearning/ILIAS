<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * A predicate to compare two values.
 */
class PredicateNeq extends PredicateComparison {
	protected $left;
	protected $right;
}
