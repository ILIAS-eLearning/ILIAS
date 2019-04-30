<?php

namespace ILIAS\RuleEnginge\Target\ilSqlVisitor;

use ILIAS\RuleEngine\Entity\Entity;
use ILIAS\RuleEngine\Compiler\CompilerTarget;

class ilSqlVisitor implements CompilerTarget {

	public function __construct() {
	}

	/**
	 * {@inheritdoc}
	 */
	//TODO exctract
	public function getOperators():array {
		return  [   'and' =>  function ($a, $b) { return sprintf('(%s AND %s)', $a, $b); },
                    'or' =>   function ($a, $b) { return sprintf('(%s OR %s)', $a, $b); },
                    'not' =>  function ($a)     { return sprintf('NOT (%s)', $a); },
                     '=' =>    function ($a, $b) { return sprintf('%s = %s', $a, $b); },
                     '!=' =>   function ($a, $b) { return sprintf('%s != %s', $a, $b); },
                     '>' =>    function ($a, $b) { return sprintf('%s > %s', $a,  $b); },
                     '>=' =>   function ($a, $b) { return sprintf('%s >= %s', $a,  $b); },
                     '<' =>    function ($a, $b) { return sprintf('%s < %s', $a,  $b); },
			         '<=' =>   function ($a, $b) { return sprintf('%s <= %s', $a,  $b); },
			         'in' =>   function ($a, $b) { return sprintf('%s IN %s', $a, $b[0] === '(' ? $b : '('.$b.')'); },
                     'like' => function ($a, $b) { return sprintf('%s LIKE %s', $a, $b); }];
	}




	public function supports($target, string $mode): bool {
		return $target instanceof Entity;
	}


	/**
	 * {@inheritdoc}
	 */
	public function visitParameter($element) {
		//TODO
	}


	/**
	 * {@inheritdoc}
	 */
	public function visitOperator($element) {
		$parameters = [];
		//TODO Visit
		$operator = $element->getName();
		//TODO by Visit?
		$sql = "";

		if (in_array($operator, [ 'and', 'or', 'not' ], true)) {
			return $sql;
		}
	}
}
