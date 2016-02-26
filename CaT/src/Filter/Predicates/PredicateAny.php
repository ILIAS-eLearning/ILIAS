<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * A predicate that matches iff any predicate matches.
 */
class PredicateAny extends PredicateBundle {
	protected $subs;
}