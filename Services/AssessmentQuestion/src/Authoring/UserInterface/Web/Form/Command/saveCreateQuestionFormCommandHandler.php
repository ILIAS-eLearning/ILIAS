<?php
namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;


use ILIAS\AssessmentQuestion\Authoring\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\Authoring\Application\AuthoringApplicationServiceSpec;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionContainer;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Input\questionTypeSelect;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandHandlerContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;

class saveCreateQuestionFormCommandHandler implements CommandHandlerContract {

	public function handle(CommandContract $command) {
		global $DIC;
		/**
		 * @var saveCreateQuestionFormCommand $command
		 */
		$cmd = $command;

		$form = new CreateQuestionForm();
		$result = $form->getForm($cmd->getCreateQuestionFormSpec())->withRequest($cmd->getRequest())->getData();

		$uuid = new \ILIAS\Data\UUID\Factory();

		//TODO
		$authoring_service_spec = new AuthoringApplicationServiceSpec(
			new DomainObjectId($uuid->uuid4()->toString()),
			71,
			new QuestionContainer(67),
			$result[0]['question_type']);
		$authoring_service = new AuthoringApplicationService($authoring_service_spec);
		$authoring_service->CreateQuestion();
		 print_r($result); exit;
	}
}