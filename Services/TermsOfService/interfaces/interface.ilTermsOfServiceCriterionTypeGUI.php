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
	 * @param array $config
	 */
	public function appendOption(\ilRadioGroupInputGUI $option, array $config);

	/**
	 * @param ilPropertyFormGUI $form
	 * @return array
	 */
	public function getConfigByForm(\ilPropertyFormGUI $form): array;
}