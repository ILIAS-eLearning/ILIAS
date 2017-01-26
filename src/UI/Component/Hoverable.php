<?php
namespace ILIAS\UI\Component;

/**
 * Interface Hoverable
 *
 * Describes a component that can trigger signals of other components on hover.
 *
 * @package ILIAS\UI\Component
 */
interface Hoverable extends Triggerer {

	/**
	 * Get a component like this, triggering a signal of another component on hover.
	 * Note: Any previous signals registered on hover are replaced.
	 *
	 * @param string $signal A signal of another component
	 * @param array $options Key/value pair of options passed to the signal when being triggered
	 *
	 * @return $this
	 */
	public function withOnHover($signal, $options = array());

	/**
	 * Get a component like this, triggering a signal of another component on hover.
	 * In contrast to withOnHover, the signal is appended to existing signals for the hover event
	 *
	 * @param string $signal
	 * @param array $options
	 *
	 * @return $this
	 */
	public function appendOnHover($signal, $options = array());

}