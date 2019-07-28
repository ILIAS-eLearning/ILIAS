<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use Psr\Http\Message\ServerRequestInterface;

class saveCreateQuestionFormCommand implements CommandContract {
	/**
	 * @var ServerRequestInterface
	 */
	protected $request;
	/**
	 * @var CreateQuestionFormSpec
	 */
	protected $create_question_form_spec;

	public function __construct(
		CreateQuestionFormSpec $create_question_form_spec,
		ServerRequestInterface $request) {
		$this->request = $request;
		$this->create_question_form_spec = $create_question_form_spec;
	}


	/**
	 * @return ServerRequestInterface
	 */
	public function getRequest(): ServerRequestInterface {
		return $this->request;
	}


	/**
	 * @return CreateQuestionFormSpec
	 */
	public function getCreateQuestionFormSpec(): CreateQuestionFormSpec {
		return $this->create_question_form_spec;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public static function getName():string {
		$reflection_class = new \ReflectionClass(static::class);
		return $reflection_class->getShortName();
	}
}
