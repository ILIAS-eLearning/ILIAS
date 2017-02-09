<?php
namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component\Signal;

/**
 * Trait Triggerer
 *
 * Provides helper methods and default implementation for components acting as triggerer
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
trait Triggerer {

	/**
	 * @var \ILIAS\UI\Implementation\Component\TriggeredSignalInterface[]
	 */
	protected $triggered_signals = array();

	/**
	 * Append a triggered signal to other signals of the same event
	 *
	 * @param Signal $signal
	 * @param string $event
	 * @return $this
	 */
	protected function appendTriggeredSignal(Signal $signal, $event) {
		$clone = clone $this;
		if (!is_array($clone->triggered_signals[$event])) {
			$clone->triggered_signals[$event] = array();
		}
		$clone->triggered_signals[$event][] = new TriggeredSignal($signal, $event);
		return $clone;
	}

	/**
	 * Add a triggered signal, replacing any other signals registered on the same event
	 *
	 * @param Signal $signal
	 * @param string $event
	 * @return $this
	 */
	protected function addTriggeredSignal(Signal $signal, $event) {
		$clone = clone $this;
		$clone->triggered_signals[$event] = array();
		$clone->triggered_signals[$event][] = new TriggeredSignal($signal, $event);
		return $clone;
	}

	/**
	 * @return \ILIAS\UI\Implementation\Component\TriggeredSignalInterface[]
	 */
	public function getTriggeredSignals() {
		return $this->flattenArray($this->triggered_signals);
	}

	/**
	 * @return $this
	 */
	public function withResetTriggeredSignals() {
		$clone = clone $this;
		$clone->triggered_signals = array();
		return $clone;
	}

	/**
	 * Flatten a multidimensional array to a single dimension
	 *
	 * @param array $array
	 * @return array
	 */
	private function flattenArray(array $array) {
		$flatten = array();
		array_walk_recursive($array, function ($a) use (&$flatten) {
			$flatten[] = $a;
		});
		return $flatten;
	}

}