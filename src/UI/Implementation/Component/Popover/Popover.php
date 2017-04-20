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
	 * @var array
	 */
	protected static $positions = array(
		self::POS_AUTO,
		self::POS_HORIZONTAL,
		self::POS_VERTICAL,
	);

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var Component\Component[]
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $position = self::POS_AUTO;

	/**
	 * @var Signal
	 */
	protected $show_signal;

	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;

	/**
	 * @param string $title
	 * @param string $content
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct($title, $content, SignalGeneratorInterface $signal_generator) {
		$this->checkStringArg('title', $title);
		$content = $this->toArray($content);
		$types = array(Component\Component::class);
		$this->checkArgListElements('content', $content, $types);
		$this->title = $title;
		$this->content = $content;
		$this->position = self::POS_AUTO;
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
	public function withPosition($position) {
		$this->checkArgIsElement('position', $position, self::$positions, implode(',', self::$positions));
		$clone = clone $this;
		$clone->position = $position;
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
	 * Init any signals of this component
	 */
	protected function initSignals() {
		$this->show_signal = $this->signal_generator->create();
	}

}