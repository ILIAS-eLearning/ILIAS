<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilPasswordEncoderConfigurationFormAware
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
interface ilPasswordEncoderConfigurationFormAware
{
	/**
	 * Called when an encoder should build individual form parts for the user interface
	 * @param ilPropertyFormGUI $form
	 */
	public function buildForm(ilPropertyFormGUI $form);

	/**
	 * Called if an encoder should validate a request concerning business rules
	 * @param ilPropertyFormGUI $form
	 * @return boolean
	 */
	public function validateForm(ilPropertyFormGUI $form);

	/**
	 * 
	 * @param ilPropertyFormGUI $form
	 * @return mixed
	 */
	public function saveForm(ilPropertyFormGUI $form);

	/**
	 * A client should call this method when the specific encoder is selected
	 */
	public function onSelection();
} 