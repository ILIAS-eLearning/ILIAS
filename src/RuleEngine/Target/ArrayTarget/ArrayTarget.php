<?php

namespace ILIAS\RuleEnginge\Target\ArrayTarget;

use ILIAS\RuleEngine\Target\Target;

class ArrayTarget implements Target {

	/**
	 * {@inheritdoc}
	 */
	public function supports($target, string $mode): bool {
		if ($mode === self::MODE_APPLY_FILTER) {
			return false;
		}

		if ($mode === self::MODE_FILTER) {
			return is_array($target);
		}

		return is_array($target);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOperators(): array {
		return [
			'and' => function ($a, $b) { return sprintf('(%s && %s)', $a, $b); },
			'or' => function ($a, $b) { return sprintf('(%s || %s)', $a, $b); },
			'not' => function ($a) { return sprintf('!(%s)', $a); },
			'=' => function ($a, $b) { return sprintf('%s == %s', $a, $b); },
			'is' => function ($a, $b) { return sprintf('%s === %s', $a, $b); },
			'!=' => function ($a, $b) { return sprintf('%s != %s', $a, $b); },
			'>' => function ($a, $b) { return sprintf('%s > %s', $a, $b); },
			'>=' => function ($a, $b) { return sprintf('%s >= %s', $a, $b); },
			'<' => function ($a, $b) { return sprintf('%s < %s', $a, $b); },
			'<=' => function ($a, $b) { return sprintf('%s <= %s', $a, $b); },
			'in' => function ($a, $b) { return sprintf('in_array(%s, %s)', $a, $b); }
		];
	}
}
