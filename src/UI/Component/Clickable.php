<?php
namespace ILIAS\UI\Component;

/**
 * Interface Clickable
 *
 * Describes a component that can trigger signals of other components on click.
 *
 * @package ILIAS\UI\Component
 */
interface Clickable extends Triggerer {

	/**
	 * Get a component like this, triggering a signal of another component on click.
	 * Note: Any previous signals registered on click are replaced.
	 *
	 * @param string $signal A signal of another component
	 * @param array $options Key/value pair of options passed to the signal when being triggered
	 *
	 * @return $this
	 */
	public function withOnClick($signal, array $options = array());

	/**
	 * Get a component like this, triggering a signal of another component on click.
	 * In contrast to withOnClick, the signal is appended to existing signals for the click event
	 *
	 * @param string $signal
	 * @param array $options
	 *
	 * @return $this
	 */
	public function appendOnClick($signal, array $options = array());

}