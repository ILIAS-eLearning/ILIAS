<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree;

use ILIAS\UI\Component\Tree as ITree;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Tree Control
 */
abstract class Tree implements ITree\Tree
{
	use ComponentHelper;

	/**
	 * @var mixed
	 */
	protected $environment;

	/**
	 * @var mixed
	 */
	protected $data;

	/**
	 * @var TreeRecursion
	 */
	protected $recursion;

	/**
	 * @var bool
	 */
	protected $highlight_nodes_on_click = false;


	public function __construct(ITree\TreeRecursion $recursion)
	{
		$this->recursion = $recursion;
	}

	/**
	 * @inheritdoc
	 */
	public function withEnvironment($environment): ITree\Tree
	{
		$clone = clone $this;
		$clone->environment = $environment;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withData($data): ITree\Tree
	{
		$clone = clone $this;
		$clone->data = $data;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @inheritdoc
	 */
	public function getRecursion(): ITree\TreeRecursion
	{
		return $this->recursion;
	}


	/**
	 * @inheritdoc
	 */
	public function withHighlightOnNodeClick(bool $highlight_nodes_on_click): ITree\Tree
	{
		$clone = clone $this;
		$clone->highlight_nodes_on_click = $highlight_nodes_on_click;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getHighlightOnNodeClick(): bool
	{
		return $this->highlight_nodes_on_click;
	}
}
