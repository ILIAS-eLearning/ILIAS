<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceCriterionTypeGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceCriterionTypeGUI
{
	/**
	 * @param ilRadioGroupInputGUI $option
	 */
	public function appendOption(\ilRadioGroupInputGUI $option);

	/**
	 * @param ilPropertyFormGUI $form
	 * @return array
	 */
	public function getConfigByForm(\ilPropertyFormGUI $form): array;

	/**
	 * @param array $formData The key/value pair array of the \ilPropertyFormGUI legacy forms
	 * @param array $config
	 */
	public function setFormValues(array &$formData, array $config);
}