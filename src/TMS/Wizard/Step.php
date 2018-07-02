<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Wizard;

/**
 * This is one step in the booking process of the user.
 */
interface Step {
	/**
	 * Get a label for this step in the process.
	 *
	 * @return	string
	 */
	public function getLabel();

	/**
	 * Get a description for this step in the process.
	 *
	 * @return	string
	 */
	public function getDescription();

	/**
	 * Get the form to prompt the user.
	 *
	 * @param	\ilPropertyFormGUI	$form
	 * @return	void
	 */
	public function appendToStepForm(\ilPropertyFormGUI $form);

	/**
	 * Get the data the step needs to store until the end of the process, based
	 * on the form.
	 *
	 * The data needs to be plain PHP data that can be serialized/unserialized
	 * via json.
	 *
	 * If null is returned, the form was not displayed correctly and needs to
	 *
	 * @param	\ilPropertyFormGUI	$form
	 * @return	mixed|null
	 */
	public function getData(\ilPropertyFormGUI $form);

	/**
	 * Adds the saved data of previous step action to the form
	 *
	 * Data may be needs to be converted from plain PHP data in value to
	 * to set as array values
	 *
	 * @param \ilPropertyFormGUI 	$form
	 * @param mixed 	$data
	 * @return void
	 */
	public function addDataToForm(\ilPropertyFormGUI $form, $data);

	/**
	 * Use the data to append a short summary of the step data to the form.
	 *
	 * The data must be the same as the component return via getData.
	 *
	 * @param	\ilPropertyFormGUI	$form
	 * @param	mixed		$data
	 * @return	void
	 */
	public function appendToOverviewForm(\ilPropertyFormGUI $form, $data);

	/**
	 * Process the data to perform the actions in the system that are required
	 * for the step.
	 *
	 * The data must be the same as the component return via getData.
	 *
	 * The returned string should be shown as a confirmation to the user.
	 *
	 * @param	mixed   $data
	 * @return	string|null
	 */
	public function	processStep($data);
}
