<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tree;

use ILIAS\UI\Component\Tree as ITree;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements ITree\Factory
{
	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct(SignalGeneratorInterface $signal_generator)
	{
		$this->signal_generator = $signal_generator;
	}

	/**
	 * @inheritdoc
	 */
	public function node(): ITree\Node\Factory
	{
		return new Node\Factory($this->signal_generator);
	}

	/**
	 * @inheritdoc
	 */
	public function tree(ITree\TreeRecursion $recursion): ITree\Tree
	{
		return new Tree($recursion);
	}
}
