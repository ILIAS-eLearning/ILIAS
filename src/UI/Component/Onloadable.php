<?php
namespace ILIAS\UI\Component;

/**
 * Interface Onloadable
 *
 * Describes a component that can trigger signals of other components on load.
 *
 * @package ILIAS\UI\Component
 */
interface Onloadable extends Triggerer {

	/**
	 * Trigger a signal of another component on load
	 *
	 * @param string $signal A signal of another component
	 * @param array $options Key/value pair of options passed to the signal when being triggered
	 *
	 * @return $this
	 */
	public function withOnLoad($signal, array $options = array());

	/**
	 * Get a component like this, triggering a signal of another component on load.
	 * In contrast to withOnLoad, the signal is appended to existing signals for the on load event
	 *
	 * @param string $signal
	 * @param array $options
	 *
	 * @return $this
	 */
	public function appendOnLoad($signal, array $options = array());

}