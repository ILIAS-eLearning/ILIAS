<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerTypeContractMultipleChoice;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\FormCommandBusBuilder;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\saveCreateQuestionFormCommand;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\showCreateQuestionFormCommand;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;
use ILIAS\AssessmentQuestion\Play\Editor\AvailableEditors;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;

/**
 * Class ilAsqQuestionAuthoringGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionAuthoringGUI
{
	protected $asq_authoring_help_component_screen_id = "asq_authoring";

	/**
	 * ilAsqQuestionAuthoringGUI constructor.
	 * @param AuthoringServiceSpecContract $authoringQuestionServiceSpec
	 */
	public function __construct(AuthoringServiceSpecContract $authoringQuestionServiceSpec)
	{
		
	}


	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public static function getClassNameForIlCtrl():string {
		$reflection_class = new \ReflectionClass(static::class);
		return $reflection_class->getShortName();
	}
	
	public function executeCommand()
	{
		global $DIC;

		$DIC->help()->setScreenIdComponent($this->asq_authoring_help_component_screen_id);
		$next_class = $DIC->ctrl()->getNextClass($this);
		$cmd = $DIC->ctrl()->getCmd();

		switch (strtolower($next_class)) {
			default:
				switch ($cmd) {
					case showCreateQuestionFormCommand::getName():
						FormCommandBusBuilder::getFormCommandBus()->handle(
							new showCreateQuestionFormCommand($this->buildCreateQuestionFormSpec($DIC)
							)
						);
						break;
					case saveCreateQuestionFormCommand::getName():
						FormCommandBusBuilder::getFormCommandBus()->handle(
							new saveCreateQuestionFormCommand(
								$this->buildCreateQuestionFormSpec($DIC),
								$DIC->http()->request()
							)
						);
					default:
						// Unknown command
						//TODO Exception
						echo "TODO Exception";exit;
						break;
				}
				break;
		}
	}


	/**
	 * @param \ILIAS\DI\Container $DIC
	 *
	 * @return CreateQuestionFormSpec
	 * @throws ReflectionException
	 */
	protected function buildCreateQuestionFormSpec(\ILIAS\DI\Container $DIC): CreateQuestionFormSpec {

		//TODO
		$answer_types = [
			AnswerTypeContractMultipleChoice::TYPE_ID => AnswerTypeContractMultipleChoice::TYPE_ID];

		return new CreateQuestionFormSpec($DIC->ctrl()
			->getLinkTarget($this, saveCreateQuestionFormCommand::getName()), new QuestionTypeSection($answer_types));
	}


}