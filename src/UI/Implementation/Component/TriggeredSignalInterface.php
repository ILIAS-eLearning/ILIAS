<?php
namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component as C;

/**
 * Interface TriggeredSignal
 *
 * Describes a signal that is triggered by a component on an event
 *
 * @package namespace ILIAS\UI\Implementation\Component
 */
interface TriggeredSignalInterface {

	/**
	 * Get the signal that will be triggered
	 *
	 * @return C\Signal
	 */
	public function getSignal();

	/**
	 * Get the event triggering the signal
	 *
	 * @return string
	 */
	public function getEvent();

}