<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class PresentationRow implements T\PresentationRow {
	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var Signal
	 */
	protected $show_signal;

	/**
	 * @var Signal
	 */
	protected $close_signal;

	/**
	 * @var	string
	 */
	private $title;

	/**
	 * @var	string
	 */
	private $subtitle;

	/**
	 * @var	array
	 */
	private $buttons;

	/**
	 * @var	array
	 */
	private $important_fields;

	/**
	 * @var
	 */
	private $content;

	/**
	 * @var	string
	 */
	private $further_fields_headline;

	/**
	 * @var	array
	 */
	private $further_fields;

	/**
	 * @var	array
	 */
	private $data;

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
	 * Set the show and close signals for this component
	 */
	protected function initSignals() {
		$this->show_signal = $this->signal_generator->create();
		$this->close_signal = $this->signal_generator->create();
	}

	/**
	 * @inheritdoc
	 */
	public function getShowSignal() {
		return $this->show_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function getCloseSignal() {
		return $this->close_signal;
	}


	/**
	 * @inheritdoc
	 */
	public function withTitle($title) {
		$this->checkStringArg("string", $title);
		$clone = clone $this;
		$clone->title = $title;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function withSubtitle($subtitle) {
		$this->checkStringArg("string", $subtitle);
		$clone = clone $this;
		$clone->subtitle = $subtitle;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getSubtitle() {
		return $this->subtitle;
	}

	/**
	 * @inheritdoc
	 */
	public function withImportantFields(array $fields) {
		$clone = clone $this;
		$clone->important_fields = $fields;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getImportantFields() {
		return $this->important_fields;
	}


	/**
	 * @inheritdoc
	 */
	public function withContent($content) {
		$clone = clone $this;
		$clone->content = $content;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getContent() {
		return $this->content;
	}


	/**
	 * @inheritdoc
	 */
	public function withFurtherFieldsHeadline($headline) {
		$this->checkStringArg("string", $headline);
		$clone = clone $this;
		$clone->further_fields_headline = $headline;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getFurtherFieldsHeadline() {
		return $this->further_fields_headline;
	}

	/**
	 * @inheritdoc
	 */
	public function withFurtherFields(array $fields) {
		$clone = clone $this;
		$clone->further_fields = $fields;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getFurtherFields() {
		return $this->further_fields;
	}


	/**
	 * @inheritdoc
	 */
	public function withButtons(array $buttons) {
		$clone = clone $this;
		$clone->buttons = $buttons;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getButtons() {
		return $this->buttons;
	}

}
