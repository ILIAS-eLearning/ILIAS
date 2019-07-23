<?php

/**
 * Class AsqPlayService
 *
 * Service providing the needed Methods for Displaying and Answering Question
 */
class AsqPlayService {

	/**
	 * @param string      $question_uuid
	 * @param string|null $solution_uuid
	 *
	 * @return QuestionComponent
	 *
	 * Gets Question Presentation Component, if solution is given that solution
	 * will be displayed
	 */
	public function GetQuestionPresentation(string $question_uuid, string $solution_uuid = null) : QuestionComponent {
		$question = $question_repository->getQuestion($question_uuid);

		if (!is_null($solution_uuid)) {
			$solution = $question_repository->getSolution($solution_uuid);
		}

		return new QuestionComponent($question, $solution);
	}


	/**
	 * @param SolutionDto $solution
	 *
	 * @return string
	 *
	 * Saves given solution, method returns uuid of saved solution
	 */
	public function SaveUserSolution(SolutionDto $solution) : string {
		return $question_repository->saveSolution($solution);
	}


	/**
	 * @param string $question_uuid
	 * @param int    $user_id
	 *
	 * @return int
	 *
	 * Gets the users score for a question
	 */
	public function GetUserScore(string $question_uuid, int $user_id) : int {
		$solution = $question_repository->getSolutionOfUser($question_id, $user_id);
		$scoring = $question_repository->getScoringForQuestion($question_uuid);
		return $scoring->ScoreSolution($solution);
	}


	/**
	 * @param string $solution_uuid
	 *
	 * @return int
	 */
	public function GetScoreForSolution(string $solution_uuid) : int{
		$solution = $question_repository->getSolution($solution_uuid);
		$scoring = $question_repository->getScoringForQuestion($solution->getQuestionUuid());
		return $scoring->ScoreSolution($solution);
	}
}