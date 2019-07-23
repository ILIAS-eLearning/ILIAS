<?php

class QuestionComponent {

	/**
	 * QuestionComponent constructor.
	 *
	 * @param QuestionDto      $question
	 * @param SolutionDto|null $solution
	 */
	public function __construct(QuestionDto $question, SolutionDto $solution = null)
	{

	}


	/**
	 * @return string
	 *
	 * Generates HTML code to display the current question
	 */
	public function render() : string {

	}


	/**
	 * @return SolutionDto
	 *
	 * reads the Given solution from the POST
	 */
	public function getSolution() : SolutionDto {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new ilException("Can only get Solution from POST request");
		}
	}
}