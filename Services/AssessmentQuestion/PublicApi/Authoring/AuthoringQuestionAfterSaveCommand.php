<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;



use ILIAS\AssessmentQuestion\CQRS\Command\AbstractCommand;
use ILIAS\AssessmentQuestion\CQRS\Command\CommandContract;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;

/**
 * Class AnswerQuestionCommand
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AuthoringQuestionAfterSaveCommand extends AbstractCommand implements CommandContract {

    /**
     * @var QuestionDto
     */
    private $question_dto;

    /**
     * QuestionAnsweredCommand constructor.
     *
     * @param Answer $answer
     */
    public function __construct(int $initiating_user_id, QuestionDto $question_dto) {
        parent::__construct($initiating_user_id);
        $this->question_dto = $question_dto;
    }


    /**
     * @return Answer
     */
    public function getQuestionDto(): QuestionDto {
        return $this->question_dto;
    }
}