<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;


use ilException;

class QuestionEditorComponent  {

	/**
	 * QuestionEditorComponent constructor.
	 *
	 * @param QuestionDto $question
	 */
	public function __construct($question = null) {

	}


	/**
	 * @return string
	 *
	 * Saves the Question and returns the uuid of the newly saved question
	 */
	public function saveQuestion() : string {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new ilException("Can only get Solution from POST request");
		}
	}
}
