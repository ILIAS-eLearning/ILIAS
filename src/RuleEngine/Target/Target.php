<?php

namespace ILIAS\RuleEngine\Target;



interface Target
{
	const MODE_FILTER = 'filter';
	const MODE_APPLY_FILTER = 'apply_filter';
	const MODE_SATISFIES = 'satisfies';


	public function supports($target, string $mode): bool;

	public function getOperators(): array;
}