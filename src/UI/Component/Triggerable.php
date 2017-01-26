<?php
namespace ILIAS\UI\Component;

/**
 * Interface Triggerable
 *
 * Any component offering signals that can be triggered by other components must implement this interface.
 * Example: A modal offers signals to show and close the modal.
 * A signal is represented by a unique string identifier.
 *
 * @package ILIAS\UI\Component
 */
interface Triggerable {

	/**
	 * Get a component like this but reset (regenerate) its signals.
	 *
	 * @return $this
	 */
	public function withResetSignals();

}