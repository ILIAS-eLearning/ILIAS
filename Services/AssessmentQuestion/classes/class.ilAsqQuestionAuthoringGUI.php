<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;

/**
 * Class ilAssessmentQuestionExporter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionAuthoringGUI
{
	const CMD_CREATE_QUESTION = "createQuestion";

	/**
	 * ilAsqQuestionAuthoringGUI constructor.
	 * @param AuthoringServiceSpecContract $authoringQuestionServiceSpec
	 */
	public function __construct(AuthoringServiceSpecContract $authoringQuestionServiceSpec)
	{
		
	}
	
	public function executeCommand()
	{
		global $DIC;

		$cmd = $DIC->ctrl()->getCmd();
		echo $cmd; exit;
	}
}