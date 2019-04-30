<?php

namespace ILIAS\RuleEnginge\Target\ArrayVisitor;

use ILIAS\RuleEnginge\Compiler\CompilerTarget;
use ILIAS\RuleEnginge\Compiler\Context;
use ILIAS\RuleEnginge\Compiler\Executor;
use ILIAS\RuleEnginge\Compiler\Rule;

class ArrayVisitor implements CompilerTarget
{
    /**
     * {@inheritdoc}
     */
    public function supports($target, $mode):bool
    {
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
	protected function createVisitor(Context $context) {
		return new ArrayVisitor($context, $this->getOperators());
	}



    /**
     * {@inheritdoc}
     */
    public function getOperators():array
    {
        return  [   'and' => function ($a, $b) { return sprintf('(%s && %s)', $a, $b); },
	                'or' =>  function ($a, $b) { return sprintf('(%s || %s)', $a, $b); },
                    'not' => function ($a)     { return sprintf('!(%s)', $a); },
	                '=' =>   function ($a, $b) { return sprintf('%s == %s', $a, $b); },
	                'is' =>  function ($a, $b) { return sprintf('%s === %s', $a, $b); },
                    '!=' =>  function ($a, $b) { return sprintf('%s != %s', $a, $b); },
                    '>' =>   function ($a, $b) { return sprintf('%s > %s', $a, $b); },
                    '>=' =>  function ($a, $b) { return sprintf('%s >= %s', $a, $b); },
                    '<' =>   function ($a, $b) { return sprintf('%s < %s', $a, $b); },
                    '<=' =>  function ($a, $b) { return sprintf('%s <= %s', $a, $b); },
                    'in' =>  function ($a, $b) { return sprintf('in_array(%s, %s)', $a, $b); }];
    }


	public function compile(Rule $rule, Context $compilationContext): Executor {
		// TODO: Implement compile() method.
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
}
