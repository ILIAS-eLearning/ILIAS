<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\FeedbackDto;

/**
 * Class QuestionDto
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionDto {

    const IL_COMPONENT_ID = 'asq';

	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var string
	 */
	private $revision_id;
	/**
	 * @var string
	 */
	private $revision_name = "";
	/**
	 * @var int
	 */
	private $container_obj_id = "";
    /**
     * var string
     */
    private $il_component_id = self::IL_COMPONENT_ID;
	/**
	 * @var QuestionData
	 */
	private $data;
	/**
	 * @var QuestionPlayConfiguration
	 */
	private $play_configuration;
	/**
	 * @var QuestionLegacyData
	 */
	private $legacy_data;
	/**
	 * @var AnswerOptions
	 */
	private $answer_options;
    /**
     * @var int
     */
	private $question_int_id;
    /**
     * @var ContentEditingModeDto
     */
	private $content_editing_mode;
    /**
     * @var FeedbackDto
     */
	private $feedback_correct;
    /**
     * @var FeedbackDto
     */
	private $feedback_wrong;

    /**
	 * @param Question $question
	 *
	 * @return QuestionDto
	 */
	public static function CreateFromQuestion(Question $question) : QuestionDto {
		$dto = new QuestionDto();
		$dto->id = $question->getAggregateId()->getId();
        $dto->container_obj_id = $question->getContainerObjId();
		$dto->question_int_id = $question->getQuestionIntId();
        
		if ($question->getRevisionId() !== null) {
			$dto->revision_id = $question->getRevisionId()->getKey();
			$dto->revision_name = $question->getRevisionName();
		}

		$dto->data = $question->getData();
		$dto->play_configuration = $question->getPlayConfiguration();
		$dto->answer_options = $question->getAnswerOptions();
		$dto->legacy_data = $question->getLegacyData();

		$dto->content_editing_mode = ContentEditingModeDto::createFromContentEditingMode(
		    $question->getContentEditingMode()
        );

		/*$dto->feedback_correct = FeedbackDto::createFromFeedback(
            $question->getFeedbackCorrect()
        );

		$dto->feedback_wrong = FeedbackDto::createFromFeedback(
            $question->getFeedbackWrong()
        );*/

		return $dto;
	}
	
	public function __construct() {
	    $this->answer_options = new AnswerOptions();
	}

	/**
     * @return int
     */
    public function getQuestionIntId()
    {
        return $this->question_int_id;
    }

    /**
     * @param int $question_int_id
     */
    public function setQuestionIntId($question_int_id)
    {
        $this->question_int_id = $question_int_id;
    }

    /**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}


	/**
	 * @param string $id
	 */
	public function setId(string $id) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getRevisionId(): string {
		return $this->revision_id;
	}


    /**
     * @param string $revision_id
     */
    public function setRevisionId(string $revision_id) : void
    {
        $this->revision_id = $revision_id;
    }

    /**
     * @param string $revision_name
     */
    public function setRevisionName(string $revision_name) : void
    {
        $this->revision_name = $revision_name;
    }
    
	/**
	 * @return string
	 */
	public function getRevisionName(): string {
		return $this->revision_name;
	}

	/**
	 * @return number
	 */
	public function getContainerObjId()
	{
	    return $this->container_obj_id;
	}
	
	/**
	 * @param number $container_obj_id
	 */
	public function setContainerObjId($container_obj_id)
	{
	    $this->container_obj_id = $container_obj_id;
	}


    /**
     * @return string
     */
    public function getIlComponentid() : string
    {
        return $this->il_component_id;
    }

	
	/**
	 * @return QuestionData
	 */
	public function getData(): ?QuestionData {
		return $this->data;
	}

	/**
	 * @param QuestionData $data
	 */
	public function setData(QuestionData $data): void {
		$this->data = $data;
	}


	/**
	 * @return QuestionLegacyData
	 */
	public function getLegacyData(): ?QuestionLegacyData {
		return $this->legacy_data;
	}


	/**
	 * @param QuestionLegacyData $legacy_data
	 */
	public function setLegacyData(?QuestionLegacyData $legacy_data): void {
		$this->legacy_data = $legacy_data;
	}

	/**
	 * @return QuestionPlayConfiguration
	 */
	public function getPlayConfiguration(): ?QuestionPlayConfiguration {
		return $this->play_configuration;
	}


	/**
	 * @param QuestionPlayConfiguration $play_configuration
	 */
	public function setPlayConfiguration(QuestionPlayConfiguration $play_configuration): void {
		$this->play_configuration = $play_configuration;
	}


	/**
	 * @return AnswerOptions
	 */
	public function getAnswerOptions(): AnswerOptions {
		return $this->answer_options;
	}


	/**
	 * @param AnswerOptions $answer_options
	 */
	public function setAnswerOptions(AnswerOptions $answer_options): void {
		$this->answer_options = $answer_options;
	}


    /**
     * @return ContentEditingModeDto
     */
    public function getContentEditingMode() : ContentEditingModeDto
    {
        return $this->content_editing_mode;
    }


    /**
     * @return FeedbackDto
     */
    public function getFeedbackCorrect() : FeedbackDto
    {
        return $this->feedback_correct;
    }


    /**
     * @return FeedbackDto
     */
    public function getFeedbackWrong() : FeedbackDto
    {
        return $this->feedback_wrong;
    }

}