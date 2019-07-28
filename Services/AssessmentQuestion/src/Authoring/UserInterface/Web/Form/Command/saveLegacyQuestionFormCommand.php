<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\EditQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;
use Psr\Http\Message\ServerRequestInterface;

class saveLegacyQuestionFormCommand implements CommandContract {
	/**
	 * @var ServerRequestInterface
	 */
	protected $request;
	/**
	 * @var EditQuestionFormSpec
	 */
	protected $question_form_spec;

	public function __construct(
		EditQuestionFormSpec $question_form_spec) {

		$this->question_form_spec = $question_form_spec;
	}


	/**
	 * @return ServerRequestInterface
	 */
	public function getRequest(): ServerRequestInterface {
		return $this->request;
	}


	/**
	 * @return EditQuestionFormSpec
	 */
	public function getEditQuestionFormSpec(): EditQuestionFormSpec {
		return $this->question_form_spec;
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
