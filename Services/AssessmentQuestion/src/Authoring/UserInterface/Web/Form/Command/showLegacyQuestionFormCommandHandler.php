<?php
namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;


use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionRepository;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\EditQuestionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Input\questionTypeSelect;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandHandlerContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;

class showLegacyQuestionFormCommandHandler implements CommandHandlerContract {

	public function handle(CommandContract $command) {
		global $DIC;
		/**
		 * @var showLegacyQuestionFormCommand $command
		 */
		$cmd = $command;

		//TODO Filter
		$question_uuid = $_GET['question_uuid'];

		//TODO
		$arr_classes = [];
		$cmd_class = '';
		foreach($DIC->ctrl()->getCallHistory() as $arr) {
			$arr_classes[] = $arr['class'];
			$cmd_class  = $arr['class'];
		}


		$DIC->ctrl()->setParameterByClass($cmd_class,'question_uuid',$question_uuid);



		$question = QuestionRepository::getInstance()->getAggregateRootById(new DomainObjectId($_GET['question_uuid']));
		$form = new EditQuestionForm($question,$command->getQuestionFormSpec()->getFormPostUrl());
		$html = $form->getHTML();
		$DIC->ui()->mainTemplate()->setContent($html);
	}
}