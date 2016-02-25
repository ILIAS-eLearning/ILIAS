<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * A predicate to compare two values.
 */
class PredicateLt extends PredicateComparison {
	protected $left;
	protected $right;
}