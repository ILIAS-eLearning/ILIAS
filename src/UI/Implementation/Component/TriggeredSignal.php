<?php
namespace ILIAS\UI\Implementation\Component;

/**
 * Class TriggeredSignal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class TriggeredSignal implements  \ILIAS\UI\Component\TriggeredSignal {

	/**
	 * @var string
	 */
	private $signal;

	/**
	 * @var string
	 */
	private $event;

	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @param string $signal
	 * @param string $event
	 * @param array $options
	 */
	public function __construct($signal, $event, $options = array()) {
		$this->signal = $signal;
		$this->event = $event;
		$this->options = $options;
	}

	/**
	 * @inheritdoc
	 */
	public function getSignal() {
		return $this->signal;
	}

	/**
	 * @inheritdoc
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * @inheritdoc
	 */
	public function getSignalOptions() {
		return $this->options;
	}
}