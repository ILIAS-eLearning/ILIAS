<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;

class showCreateQuestionFormCommand implements CommandContract {
	/**
	 * @var CreateQuestionFormSpec
	 */
	protected $create_question_form_spec;


	//TODO answer_types should be a Object not an array!
	//TODO TypeHinting
	public function __construct(CreateQuestionFormSpec $create_question_form_spec) {
		$this->create_question_form_spec = $create_question_form_spec;
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
