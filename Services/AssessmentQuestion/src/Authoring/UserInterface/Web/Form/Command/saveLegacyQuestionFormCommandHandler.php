<?php
namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;


use ILIAS\AssessmentQuestion\Authoring\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\Authoring\Application\AuthoringApplicationServiceSpec;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerType;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionContainer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\EditQuestionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Input\questionTypeSelect;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandHandlerContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;

class saveLegacyQuestionFormCommandHandler implements CommandHandlerContract {

	public function handle(CommandContract $command) {
		global $DIC;
		/**
		 * @var saveLegacyQuestionFormCommand $command
		 */
		$cmd = $command;

		//TODO

		$question_uuid = $_POST['aggregate_id'];


		/**
		 * @var Question $question
		 */
		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($question_uuid));


		$form = new EditQuestionForm($question,$command->getEditQuestionFormSpec()->getFormPostUrl());




		$form->setValuesByArray($_POST);


		$type = new AnswerType('multi');
		//TODO
		$authoring_service_spec = new AuthoringApplicationServiceSpec(
			new DomainObjectId($question_uuid),
			71,
			new QuestionContainer(67),
			$type->getAnswerType()
		);


		$question_data = new QuestionData($form->getItemByPostVar('title')->getValue(),'','','');
		///$question->setData($question_data);

		$authoring_service = new AuthoringApplicationService($authoring_service_spec);
		$authoring_service->SaveQuestion($question_data);

		//TODO
		$arr_classes = [];
		$cmd_class = '';
		foreach($DIC->ctrl()->getCallHistory() as $arr) {
			$arr_classes[] = $arr['class'];
			$cmd_class  = $arr['class'];
		}

		$DIC->ctrl()->setParameterByClass($cmd_class,'question_uuid',$question_uuid);
		$DIC->ctrl()->redirectByClass($arr_classes,showLegacyQuestionFormCommand::getName());

	}
}