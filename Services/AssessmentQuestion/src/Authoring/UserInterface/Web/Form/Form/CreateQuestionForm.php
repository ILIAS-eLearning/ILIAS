<?php
namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;

class CreateQuestionForm {

	public function getForm(CreateQuestionFormSpec $create_question_form_spec) {
		global $DIC;


		return $DIC->ui()->factory()->input()->container()->form()->standard($create_question_form_spec->getFormPostUrl(),[$create_question_form_spec->getQuestionTypeSection()->getSection()]);
	}


}
