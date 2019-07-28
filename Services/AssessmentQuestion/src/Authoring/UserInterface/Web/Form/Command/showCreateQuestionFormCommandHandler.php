<?php
namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;


use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Input\questionTypeSelect;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandHandlerContract;

class showCreateQuestionFormCommandHandler implements CommandHandlerContract {

	public function handle(CommandContract $command) {
		global $DIC;
		/**
		 * @var ShowCreateQuestionFormCommand $command
		 */
		$cmd = $command;

		$form = new CreateQuestionForm();
		$html = $DIC->ui()->renderer()->render(
			$form->getForm($cmd->getCreateQuestionFormSpec())
		);
		$DIC->ui()->mainTemplate()->setContent($html);
	}
}