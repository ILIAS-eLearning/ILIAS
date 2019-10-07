<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\ViewControl\Pagination as PaginationInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

class Pagination implements PaginationInterface  {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var int
	 */
	protected $total_entries = 0;

	/**
	 * @var int
	 */
	protected $page_size;

	/**
	 * @var int
	 */
	protected $current_page = 0;

	/**
	 * @var Signal
	 */
	protected $internal_signal;

	/**
	 * @var string | null
	 */
	protected $target_url;

	/**
	 * @var string
	 */
	protected $parameter_name = "pagination_offset";

	/**
	 * @var int | null
	 */
	protected $max_pages_shown;

	/**
	 * @var int | null
	 */
	protected $dd_threshold;

	/**
	 * @var string
	 */
	protected $dropdown_label;


	public function __construct(SignalGeneratorInterface $signal_generator) {
		$this->signal_generator = $signal_generator;
		$this->initSignals();
		$this->dropdown_label = self::DEFAULT_DROPDOWN_LABEL;
	}

	/**
	 * @inheritdoc
	 */
	public function withResetSignals()
	{
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}

	/**
	 * Set the internal signals for this component
	 *
	 * @return void
	 */
	protected function initSignals()
	{
		$this->internal_signal = $this->signal_generator->create();
	}

	/**
	 * Get the internal signal that is triggered on click of a button.
	 */
	public function getInternalSignal(): Signal
	{
		return $this->internal_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function withTargetURL(string $url, string $parameter_name): PaginationInterface
	{
		$this->checkStringArg("url", $url);
		$this->checkStringArg("parameter_name", $parameter_name);
		$clone = clone $this;
		$clone->target_url = $url;
		$clone->parameter_name = $parameter_name;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTargetURL()
	{
		return $this->target_url;
	}

	/**
	 * @inheritdoc
	 */
	public function getParameterName(): string
	{
		return $this->parameter_name;
	}

	/**
	 * @inheritdoc
	 */
	public function withTotalEntries(int $total): PaginationInterface
	{
		$this->checkIntArg("total", $total);
		$clone = clone $this;
		$clone->total_entries = $total;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withPageSize(int $size): PaginationInterface
	{
		$this->checkIntArg("size", $size);
		//raise, if size < 1
		$clone = clone $this;
		$clone->page_size = $size;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getPageSize(): int
	{
		return $this->page_size;
	}

	/**
	 * @inheritdoc
	 */
	public function withCurrentPage(int $page): PaginationInterface
	{
		$this->checkIntArg("page", $page);
		$clone = clone $this;
		$clone->current_page = $page;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentPage(): int
	 {
		return $this->current_page;
	}

	/**
	 * @inheritdoc
	 */
	public function getOffset(): int
	{
		$offset = $this->page_size * $this->current_page;
		return $offset;
	}

	/**
	 * @inheritdoc
	 */
	public function withOnSelect(Signal $signal): PaginationInterface
	{
		return $this->withTriggeredSignal($signal, 'select');
	}

	/**
	 * @inheritdoc
	 */
	public function getNumberOfPages(): int
	{
		$pages = ceil($this->total_entries / $this->page_size);
		return (int)$pages;
	}

	/**
	 * @inheritdoc
	 */
	public function withMaxPaginationButtons(int $amount): PaginationInterface
	{
		$this->checkIntArg("amount", $amount);
		$clone = clone $this;
		$clone->max_pages_shown = $amount;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getMaxPaginationButtons()
	{
		return $this->max_pages_shown;
	}

	/**
	 * @inheritdoc
	 */
	public function getPageLength(): int
	{
		if($this->getOffset() + $this->page_size > $this->total_entries) {
			return $this->total_entries - $this->getOffset();
		}
		return $this->page_size;
	}

	/**
	 * @inheritdoc
	 */
	public function withDropdownAt(int $amount): PaginationInterface
	{
		$this->checkIntArg("amount", $amount);
		$clone = clone $this;
		$clone->dd_threshold = $amount;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getDropdownAt()
	{
		return $this->dd_threshold;
	}

	/**
	 * @inheritdoc
	 */
	public function withDropdownLabel(string $template): PaginationInterface
	{
		$clone = clone $this;
		$clone->dropdown_label = $template;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getDropdownLabel(): string
	{
		return $this->dropdown_label;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultDropdownLabel(): string
	{
		return self::DEFAULT_DROPDOWN_LABEL;
	}

}
