<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

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

	/**
	 * @return string
	 */
	public function getIdentPresentation(): string;

	/**
	 * @param array $config
	 * @param Factory $uiFactory
	 * @return Component
	 */
	public function getValuePresentation(array $config, Factory $uiFactory): Component;
}