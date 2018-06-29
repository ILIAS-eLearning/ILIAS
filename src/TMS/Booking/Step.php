<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

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
	 * Get the priority of the step.
	 *
	 * Lesser priorities means the step should be performed earlier.
	 *
	 * @return	int
	 */
	public function getPriority();

	/**
	 * Find out if this step is applicable for the booking process of the
	 * given user.
	 *
	 * @param	int	$usr_id
	 * @return	bool
	 */
	public function isApplicableFor($usr_id);

	/**
	 * Get the form to prompt the user.
	 *
	 * If $post is supplied, the form should be filled with the supplied values.
	 *
	 * @param	\ilPropertyFormGUI	$form
	 * @param 	int 	$usr_id
	 * @return	void
	 */
	public function appendToStepForm(\ilPropertyFormGUI $form, $usr_id);

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
	 * @param	mixed		$data
	 * @param	\ilPropertyFormGUI	$form
	 * @param 	int 	$usr_id
	 * @return	void
	 */
	public function appendToOverviewForm($data, \ilPropertyFormGUI $form, $usr_id);

	/**
	 * Process the data to perform the actions in the system that are required
	 * for the step.
	 *
	 * The data must be the same as the component return via getData.
	 *
	 * The returned string should be shown as a confirmation to the user.
	 *
	 * @param	int     $crs_ref_id
	 * @param	int     $usr_id
	 * @param	mixed   $data
	 * @return	string|null
	 */
	public function	processStep($crs_ref_id, $usr_id, $data);
}

