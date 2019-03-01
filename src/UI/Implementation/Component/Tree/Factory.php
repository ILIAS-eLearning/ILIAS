<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tree;

use ILIAS\UI\Component\Tree as ITree;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements ITree\Factory
{
	/**
	 * @inheritdoc
	 */
	public function node(): ITree\Node\Factory
	{
		return new Node\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function tree(ITree\TreeRecursion $recursion): ITree\Tree
	{
		return new Tree($recursion);
	}
}
