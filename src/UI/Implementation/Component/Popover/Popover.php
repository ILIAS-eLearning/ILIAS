<?php

namespace ILIAS\UI\Implementation\Component\Popover;

use \ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Popover implements Component\Popover\Popover {

	use ComponentHelper;

	const POS_AUTO = 'auto';
	const POS_VERTICAL = 'vertical';
	const POS_HORIZONTAL = 'horizontal';

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var Component\Component[]
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $position = self::POS_AUTO;

	/**
	 * @var string
	 */
	protected $ajax_content_url = '';

	/**
	 * @var Signal
	 */
	protected $show_signal;

	/**
	 * @var ReplaceContentSignal
	 */
	protected $replace_content_signal;

	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @param Component\Component|Component\Component[] $content
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct($content, SignalGeneratorInterface $signal_generator) {
		$content = $this->toArray($content);
		$types = array(Component\Component::class);
		$this->checkArgListElements('content', $content, $types);
		$this->content = $content;
		$this->signal_generator = $signal_generator;
		$this->initSignals();
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
	public function getContent() {
		return $this->content;
	}

	/**
	 * @inheritdoc
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @inheritdoc
	 */
	public function getAsyncContentUrl() {
		return $this->ajax_content_url;
	}

	/**
	 * @inheritdoc
	 */
	public function withVerticalPosition() {
		$clone = clone $this;
		$clone->position = self::POS_VERTICAL;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withHorizontalPosition() {
		$clone = clone $this;
		$clone->position = self::POS_HORIZONTAL;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withAsyncContentUrl($url) {
		$this->checkStringArg('url', $url);
		$clone = clone $this;
		$clone->ajax_content_url = $url;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withTitle($title) {
		$this->checkStringArg('title', $title);
		$clone = clone $this;
		$clone->title = $title;
		return $clone;
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
	 * @inheritdoc
	 */
	public function getShowSignal() {
		return $this->show_signal;
	}

	/**
	 * @inheritdoc
	 */
	public function getReplaceContentSignal() {
		return $this->replace_content_signal;
	}


	/**
	 * Init any signals of this component
	 */
	protected function initSignals() {
		$this->show_signal = $this->signal_generator->create();
		$this->replace_content_signal = $this->signal_generator->create("ILIAS\\UI\\Implementation\\Component\\Popover\\ReplaceContentSignal");
	}
}