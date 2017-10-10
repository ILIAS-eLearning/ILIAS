<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

class Pagination implements C\ViewControl\Pagination  {
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
	protected $select_signal;

	/**
	 * @var string
	 */
	protected $target_url;

	/**
	 * @var string
	 */
	protected $paramter_name = "pagination_offset";

	/**
	 * @var int | null
	 */
	protected $max_entries;


	public function __construct(SignalGeneratorInterface $signal_generator) {
		$this->signal_generator = $signal_generator;
		$this->initSignals();
	}

	/**
	 * @inheritdoc
	 */
	public function withResetSignals() {
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}

	/**
	 * Set the signals for this component
	 */
	protected function initSignals() {
		$this->select_signal = $this->signal_generator->create();
	}

	/**
	 * @inheritdoc
	 */
	public function getSelectSignal() {
		return $this->select_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function withTargetURL($url, $paramter_name) {
		$this->checkStringArg("url", $url);
		$this->checkStringArg("paramter_name", $paramter_name);
		$clone = clone $this;
		$clone->target_url = $url;
		$clone->paramter_name = $paramter_name;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTargetURL() {
		return $this->target_url;
	}

	/**
	 * @inheritdoc
	 */
	public function getParameterName() {
		return $this->paramter_name;
	}

	/**
	 * @inheritdoc
	 */
	public function withTotalEntries($total) {
		$this->checkIntArg("total", $total);
		$clone = clone $this;
		$clone->total_entries = $total;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withPageSize($size) {
		$this->checkIntArg("size", $size);
		//raise, if size < 1
		$clone = clone $this;
		$clone->page_size = $size;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getPageSize() {
		return $this->page_size;
	}

	/**
	 * @inheritdoc
	 */
	public function withCurrentPage($page) {
		$this->checkIntArg("page", $page);
		$clone = clone $this;
		$clone->current_page = $page;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentPage() {
		return $this->current_page;
	}

	/**
	 * @inheritdoc
	 */
	public function getOffset() {
		$offset = $this->page_size * $this->current_page;
		return $offset;
	}

	/**
	 * @inheritdoc
	 */
	public function withOnSelect(C\Signal $signal) {
		return $this->addTriggeredSignal($signal, 'select');
	}

	/**
	 * @inheritdoc
	 */
	public function getNumberOfPages() {
		$pages = floor($this->total_entries / $this->page_size);
		return (int)$pages;
	}

	/**
	 * @inheritdoc
	 */
	public function withMaxPageEntries($entries) {
		$this->checkIntArg("entries", $entries);
		$clone = clone $this;
		$clone->max_entries = $entries;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getMaxPageEntries() {
		return $this->max_entries;
	}

	/**
	 * Calculate the total number of pages.
	 *
	 * @return int
	 */
	public function getPageLength() {
		if($this->getOffset() + $this->page_size > $this->total_entries) {
			return $this->total_entries - $this->getOffset();
		}
		return $this->page_size;
	}

}
