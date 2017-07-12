<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilHtmlToPdfTransformerGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilHtmlToPdfTransformerGUI
{

	/**
	 * ilHtmlToPdfTransformerGUI constructor.
	 * @param $lng
	 */
	public function __construct($lng);

	/**
	 */
	public function populateForm();

	/**
	 */
	public function saveForm();

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendForm(ilPropertyFormGUI $form);

	/**
	 * @return bool
	 */
	public function checkForm();

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendHiddenTransformerNameToForm(ilPropertyFormGUI $form);
}