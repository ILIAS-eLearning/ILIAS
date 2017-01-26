<?php
namespace ILIAS\UI\Component;

/**
 * Interface TriggeredSignal
 *
 * Describes a signal that is triggered by a component on an event
 *
 * @package ILIAS\UI\Component
 */
interface TriggeredSignal {

	/**
	 * Get the signal that will be triggered
	 *
	 * @return string
	 */
	public function getSignal();

	/**
	 * Get the event triggering the signal
	 *
	 * @return string
	 */
	public function getEvent();

	/**
	 * Get any options that are passed to the signal (key/value) pair
	 *
	 * @return array
	 */
	public function getSignalOptions();
}