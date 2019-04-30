<?php

namespace ILIAS\RuleEnginge\Target\ilSqlVisitor;

use ILIAS\Visitor\Element;
use ILIAS\RuleEngine\Entity\Entity;

use ILIAS\RuleEnginge\Compiler\CompilerTarget;
use ILIAS\RuleEngine\Compiler\Context;
use ILIAS\RuleEngine\Compiler\Executor;
use ILIAS\RuleEngine\Compiler\Rule;

class ilSqlVisitor implements CompilerTarget {

	public function __construct() {
	}


	/**
	 * {@inheritdoc}
	 */
	protected function createVisitor(Context $context) {
		return new ilSqlVisitor($context, $this->getOperators(), $this->allowStarOperator);
	}


	/**
	 * {@inheritdoc}
	 */
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


	public function compile(Rule $rule): Executor {
		// TODO: Implement compile() method.
	}


	public function supports($target, string $mode): bool {
		return $target instanceof Entity;
	}





	public function getRuleIdentifierHint(string $rule, Context $context): string {
		// TODO: Implement getRuleIdentifierHint() method.
	}


	public function defineOperator(string $name, callable $transformer): void {
		// TODO: Implement defineOperator() method.
	}


	public function defineInlineOperator(string $name, callable $transformer): void {
		// TODO: Implement defineInlineOperator() method.
	}


	/**
	 * {@inheritdoc}
	 */
	public function visitModel(Element $element) {
		return $element->getExpression()->accept($this, $handle, $eldnah);
	}


	/**
	 * {@inheritdoc}
	 */
	public function visitParameter(Model\Parameter $element, &$handle = null, $eldnah = null) {
		$handle[] = sprintf('$parameters["%s"]', $element->getName());

		// make it a placeholder
		return '$*';
	}


	/**
	 * {@inheritdoc}
	 */
	public function visitOperator(AST\Operator $element, &$handle = null, $eldnah = null) {
		$parameters = [];
		$operator = $element->getName();
		$sql = parent::visitOperator($element, $parameters, $eldnah);

		if (in_array($operator, [ 'and', 'or', 'not' ], true)) {
			return $sql;
		}

		if ($this->operators->hasOperator($operator)) {
			return sprintf('(new \PommProject\Foundation\Where(%s, [%s]))', $sql, implode(', ', $parameters));
		}

		return sprintf('(new \PommProject\Foundation\Where("%s", [%s]))', $sql, implode(', ', $parameters));
	}
}
