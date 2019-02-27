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
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @var Signal
	 */
	protected $toggle_signal;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var bool
	 */
	protected $expanded = false;

	/**
	 * @var bool
	 */
	protected $asynch = false;

	/**
	 * @var string
	 */
	protected $asynch_url = '';

	/**
	 * @var Node[]
	 */
	protected $subnodes = [];


	public function __construct(SignalGeneratorInterface $signal_generator, string $label)
	{
		$this->signal_generator = $signal_generator;
		$this->label = $label;

		$this->initSignals();
	}

	public function withAdditionalSubnode(INode $node)
	{
		$this->subnodes[] = $node;
		return $this;
	}

	public function getSubnodes(): array
	{
		return $this->subnodes;
	}

	public function getLabel(): string
	{
		return $this->label;
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

	public function hasAsyncLoading(): bool
	{
		return $this->asynch;
	}

	public function withAsyncURL(string $url): INode
	{
		$clone = clone $this;
		$clone->asynch = true;
		$clone->asynch_url = $url;
		return $clone;
	}

	public function getAsyncURL(): string
	{
		return $this->asynch_url;
	}

	public function withOnClick(Signal $signal)
	{
		return $this->withTriggeredSignal($signal, 'click');
	}

	public function appendOnClick(Signal $signal)
	{
		return $this->appendTriggeredSignal($signal, 'click');
	}

	protected function initSignals()
	{
		$this->toggle_signal = $this->signal_generator->create();
	}
	/**
	 * @inheritdoc
	 */
	public function withResetSignals(): INode
	{
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}

	public function getToggleSignal(): Signal
	{
		return $this->toggle_signal;
	}

}
