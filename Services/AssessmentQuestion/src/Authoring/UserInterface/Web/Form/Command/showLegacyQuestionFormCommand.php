<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Command;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\CreateQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form\EditQuestionFormSpec;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command\CommandContract;

class showLegacyQuestionFormCommand implements CommandContract {
	/**
	 * @var EditQuestionFormSpec
	 */
	protected $question_form_spec;


	public function __construct(EditQuestionFormSpec $question_form_spec) {
		$this->question_form_spec = $question_form_spec;
	}

	/**
	 * @return EditQuestionFormSpec
	 */
	public function getQuestionFormSpec(): EditQuestionFormSpec {
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
