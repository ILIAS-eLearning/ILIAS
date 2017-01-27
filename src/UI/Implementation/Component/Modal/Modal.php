<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Onloadable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Base class for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class Modal implements Component\Modal\Modal {

	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var Component\SignalGenerator
	 */
	protected $signal_generator;

	/**
	 * @var string
	 */
	protected $show_signal;

	/**
	 * @var string
	 */
	protected $close_signal;

	/**
	 * @param Component\SignalGenerator $signal_generator
	 */
	public function __construct(Component\SignalGenerator $signal_generator) {
		$this->signal_generator = $signal_generator;
		$this->initSignals();
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
	public function withResetSignals() {
		$clone = clone $this;
		$clone->initSignals();
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withOnLoad($signal, array $options = array()) {
		return $this->addTriggeredSignal($signal, Component\Triggerer::EVENT_ONLOAD, $options);
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnLoad($signal, array $options = array()) {
		return $this->appendTriggeredSignal($signal, Component\Triggerer::EVENT_ONLOAD, $options);
	}


	/**
	 * Set the show and close signals for this modal
	 */
	protected function initSignals() {
		$this->show_signal = $this->signal_generator->create();
		$this->close_signal = $this->signal_generator->create();
	}

}
