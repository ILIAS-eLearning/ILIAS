<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Component\Tree\Node\Node as INode;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * A very simple Tree-Node
 */
abstract class Node implements INode
{
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var bool
	 */
	protected $expanded = false;

	/**
	 * @var Node[]
	 */
	protected $subnodes = [];

	public function getLabel(): string
	{
		return $this->label;
	}

	public function __construct(string $label)
	{
		$this->label = $label;
	}

	public function withAdditionalSubnode(INode $node): INode
	{
		$this->subnodes[] = $node;
		return $this;
	}

	public function getSubnodes(): array
	{
		return $this->subnodes;
	}

	public function withExpanded(bool $expanded): INode
	{
		$clone = clone $this;
		$clone->expanded = $expanded;
		return $clone;
	}

	public function isExpanded(): bool
	{
		return $this->expanded;
	}

	/**
	 * @inhertidoc
	 */
	public function withOnClick(Signal $signal)
	{
		return $this->withTriggeredSignal($signal, 'click');
	}

	/**
	 * @inhertidoc
	 */
	public function appendOnClick(Signal $signal)
	{
		return $this->appendTriggeredSignal($signal, 'click');
	}
}
