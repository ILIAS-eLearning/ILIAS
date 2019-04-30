<?php

namespace ILIAS\RuleEnginge\Target\SqlTarget;

use ILIAS\RuleEngine\Entity\Entity;
use ILIAS\RuleEngine\Target\Target;

class SqlTarget implements Target {

	public function __construct() {
	}

	/**
	 * {@inheritdoc}
	 */
	//TODO exctract
	public function getOperators(): array {
		return [
			'and' => function ($a, $b) { return sprintf('(%s AND %s)', $a, $b); },
			'or' => function ($a, $b) { return sprintf('(%s OR %s)', $a, $b); },
			'not' => function ($a) { return sprintf('NOT (%s)', $a); },
			'=' => function ($a, $b) { return sprintf('%s = %s', $a, $b); },
			'!=' => function ($a, $b) { return sprintf('%s != %s', $a, $b); },
			'>' => function ($a, $b) { return sprintf('%s > %s', $a, $b); },
			'>=' => function ($a, $b) { return sprintf('%s >= %s', $a, $b); },
			'<' => function ($a, $b) { return sprintf('%s < %s', $a, $b); },
			'<=' => function ($a, $b) { return sprintf('%s <= %s', $a, $b); },
			'in' => function ($a, $b) { return sprintf('%s IN %s', $a, $b[0] === '(' ? $b : '(' . $b . ')'); },
			'like' => function ($a, $b) { return sprintf('%s LIKE %s', $a, $b); }
		];
	}


	public function supports($target, string $mode): bool {
		return $target instanceof Entity;
	}
}
