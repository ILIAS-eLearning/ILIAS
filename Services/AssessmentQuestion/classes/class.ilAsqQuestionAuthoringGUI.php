<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerTypeContractMultipleChoice;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerTypeMultipleChoice;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\FormCommandBusBuilder;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\saveCreateQuestionFormCommand;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\saveLegacyQuestionFormCommand;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\showCreateQuestionFormCommand;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command\showLegacyQuestionFormCommand;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\EditQuestionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\EditQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionDataSection;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
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
	 * @var Question
	 */
	protected $question;

	/**
	 * ilAsqQuestionAuthoringGUI constructor.
	 * @param AuthoringServiceSpecContract $authoringQuestionServiceSpec
	 */
	public function __construct(AuthoringServiceSpecContract $authoringQuestionServiceSpec)
	{
		//TODO
		$question_uuid = $_GET['question_uuid'];
		$this->question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_uuid));
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
					case showLegacyQuestionFormCommand::getName():
						FormCommandBusBuilder::getFormCommandBus()->handle(
							new showLegacyQuestionFormCommand($this->buildEditQuestionFormSpec($DIC)
							)
						);
						break;
					case saveLegacyQuestionFormCommand::getName():

						FormCommandBusBuilder::getFormCommandBus()->handle(
							new saveLegacyQuestionFormCommand(
								$this->buildEditQuestionFormSpec($DIC)
							)
						);
						break;
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
			AnswerTypeMultipleChoice::TYPE_ID => AnswerTypeMultipleChoice::TYPE_ID];

		return new CreateQuestionFormSpec($DIC->ctrl()
			->getLinkTarget($this, saveCreateQuestionFormCommand::getName()), new QuestionTypeSection($answer_types));
	}

	/**
	 * @param \ILIAS\DI\Container $DIC
	 *
	 * @return CreateQuestionFormSpec
	 * @throws ReflectionException
	 */
	protected function buildEditQuestionFormSpec(\ILIAS\DI\Container $DIC): EditQuestionFormSpec {

		return new EditQuestionFormSpec($DIC->ctrl()
			->getLinkTarget($this, saveLegacyQuestionFormCommand::getName()), new QuestionDataSection($this->question));
	}


}