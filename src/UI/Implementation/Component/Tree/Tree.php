<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree;

use ILIAS\UI\Component\Tree as ITree;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Tree Control
 */
class Tree implements ITree\Tree
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


	public function __construct(ITree\TreeRecursion $recursion)
	{
		$this->recursion = $recursion;
	}

	public function withEnvironment($environment): ITree\Tree
	{
		$clone = clone $this;
		$clone->environment = $environment;
		return $clone;
	}

	public function withData($data): ITree\Tree
	{
		$clone = clone $this;
		$clone->data = $data;
		return $clone;
	}

	public function getEnvironment()
	{
		return $this->environment;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getRecursion(): ITree\TreeRecursion
	{
		return $this->recursion;
	}

}
