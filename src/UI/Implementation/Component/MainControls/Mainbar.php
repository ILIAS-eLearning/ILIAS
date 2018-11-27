<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Mainbar
 */
class Mainbar implements MainControls\Mainbar
{
	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var SignalGeneratorInterface
	 */
	private $signal_generator;

	/**
	 * @var Signal
	 */
	private $entry_click_signal;

	/**
	 * @var Signal
	 */
	private $tools_click_signal;

	/**
	 * @var Signal
	 */
	private $tools_removal_signal;

	/**
	 * @var Signal
	 */
	private $disengage_all_signal;

	/**
	 * @var array <string, Bulky|Slate>
	 */
	protected $entries;

	/**
	 * @var array <string, Slate>
	 */
	private $tool_entries = [];

	/**
	 * @var string
	 */
	private $tools_label = 'Tools';

	/**
	 * @var string | null
	 */
	private $active;

	public function __construct (SignalGeneratorInterface $signal_generator)
	{
		$this->signal_generator = $signal_generator;
		$this->initSignals();
	}

	/**
	 * @inheritdoc
	 */
	public function getEntries(): array
	{
		return $this->entries;
	}

	/**
	 * @inheritdoc
	 */
	public function withEntry(string $id, $entry): MainControls\Mainbar
	{
		$classes = [Button\Bulky::class, MainControls\Slate\Slate::class];
		$check = [$entry];
		$this->checkArgListElements("Bulky or Slate", $check, $classes);

		$clone = clone $this;
		$clone->entries[$id] = $entry;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getToolEntries(): array
	{
		return $this->tool_entries;
	}

	/**
	 * @inheritdoc
	 */
	public function withToolEntry(string $id, $entry): MainControls\Mainbar
	{
		$class = MainControls\Slate\Slate::class;
		$this->checkArgInstanceOf("Tools must be Slates", $entry, $class);

		$clone = clone $this;
		$clone->tool_entries[$id] = $entry;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withToolsLabel(string $label): MainControls\Mainbar
	{
		$clone = clone $this;
		$clone->tools_label = $label;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getToolsLabel(): string
	{
		return $this->tools_label;
	}


	/**
	 * @inheritdoc
	 */
	public function getEntryClickSignal(): Signal
	{
		return $this->entry_click_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function getToolsClickSignal(): Signal
	{
		return $this->tools_click_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function getToolsRemovalSignal(): Signal
	{
		return $this->tools_removal_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function getDisengageAllSignal(): Signal
	{
		return $this->disengage_all_signal;
	}

	/**
	 * Set the signals for this component
	 */
	protected function initSignals()
	{
		$this->entry_click_signal = $this->signal_generator->create();
		$this->tools_click_signal = $this->signal_generator->create();
		$this->tools_removal_signal = $this->signal_generator->create();
		$this->disengage_all_signal = $this->signal_generator->create();
	}

	public function withResetSignals(): MainControls\Mainbar
	{
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @inheritdoc
	 */
	public function withActive(string $active): MainControls\Mainbar
	{
		$clone = clone $this;
		$clone->active = $active;
		return $clone;
	}

}
