<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ilPropertyFormGUI;

interface AdditionalConfigSectionContract
{
	/**
	 * @param ilPropertyFormGUI $formGUI
	 */
	public function completeForm(ilPropertyFormGUI $formGUI): void;
}