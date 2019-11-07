<?php
namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;

/**
 * Class MultipleChoiceScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceScoring extends AbstractScoring
{

    function score(Answer $answer): AnswerScoreDto
    {
        $reached_points = $this->scoreAnswer($answer);

        $max_points = $this->scoreAnswer($this->getBestAnswer());

        return $this->createScoreDto($answer, $max_points, $reached_points, $this->getAnswerFeedbackType($reached_points,$max_points));
    }

    /**
     *
     * @param
     *            answer
     */
    private function scoreAnswer($answer)
    {
        $reached_points = 0;

        $selected_options = json_decode($answer->getValue(), true);

        /** @var AnswerOption $answer_option */
        foreach ($this->question->getAnswerOptions()->getOptions() as $answer_option) {
            if (in_array($answer_option->getOptionId(), $selected_options)) {
                $reached_points += $answer_option->getScoringDefinition()->getPointsSelected();
            } else {
                $reached_points += $answer_option->getScoringDefinition()->getPointsUnselected();
            }
        }
        return $reached_points;
    }

    public function getBestAnswer(): Answer
    {
        $answers = [];

        /** @var AnswerOption $answer_option */
        foreach ($this->question->getAnswerOptions()->getOptions() as $answer_option) {
            /** @var MultipleChoiceScoringDefinition $score */
            $score = $answer_option->getScoringDefinition();
            if ($score->getPointsSelected() > $score->getPointsUnselected()) {
                $answers[] = $answer_option->getOptionId();
            }
        }

        rsort($answers);

        $length = $this->question->getPlayConfiguration()
            ->getEditorConfiguration()
            ->getMaxAnswers();
        $answers = array_slice($answers, 0, $length);

        return new Answer(0, $this->question->getId(), '',0,0, json_encode($answers));
    }

    public static function readConfig()
    {
        return new MultipleChoiceScoringConfiguration();
    }
    
    public static function isComplete(Question $question): bool
    {    
        if (count($question->getAnswerOptions()->getOptions()) < 2) {
            return false;
        }
        
        foreach ($question->getAnswerOptions()->getOptions() as $option) {
            /** @var MultipleChoiceScoringDefinition $option_config */
            $option_config = $option->getScoringDefinition();
            
            if (empty($option_config->getPointsSelected()) ||
                empty($option_config->getPointsUnselected()))
            {
                return false;
            }
        }
        
        return true;
    }
}