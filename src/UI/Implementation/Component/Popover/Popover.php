<?php
namespace ILIAS\UI\Implementation\Component\Popover;

use \ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Popover implements Component\Popover\Popover {

	use ComponentHelper;

	const POS_LEFT = 'left';
	const POS_BOTTOM = 'bottom';
	const POS_TOP = 'top';
	const POS_RIGHT = 'right';
	const POS_AUTO = 'auto';

	/**
	 * @var array
	 */
	protected static $positions = array(
		self::POS_AUTO,
		self::POS_BOTTOM,
		self::POS_LEFT,
		self::POS_RIGHT,
		self::POS_TOP,
	);

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $text;

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
	 * @param string $text
	 * @param string $position
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct($title, $text, $position = 'auto', SignalGeneratorInterface $signal_generator) {
		$this->checkStringArg('title', $title);
		$this->checkStringArg('text', $text);
		$this->checkArgIsElement('position', $position, self::$positions, 'Popover position');
		$this->title = $title;
		$this->text = $text;
		$this->position = $position;
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
	public function getText() {
		return $this->text;
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
	public function getShowSignal() {
		return $this->show_signal;
	}

	/**
	 * Init any signals of this component
	 */
	protected function initSignals() {
		$this->show_signal = $this->signal_generator->create();
	}

	/**
	 * @inheritdoc
	 */
	public function withResetSignals() {
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}
}