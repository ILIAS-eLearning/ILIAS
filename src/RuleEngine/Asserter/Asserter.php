<?php

namespace ILIAS\RuleEngine\Asserter;

/**
 * Class Asserter
 *
 * Asserter: evaluate a model representing a rule.
 *
 */
interface Asserter
{
	public function evaluate(): bool;
}
