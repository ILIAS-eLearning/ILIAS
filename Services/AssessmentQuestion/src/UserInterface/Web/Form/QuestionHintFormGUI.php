<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\DomainModel\Hint\Hint;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedback;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedbackMode;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\Hint\Hints;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\AnswerCorrectFeedback;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\AnswerWrongFeedback;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\Feedback;
use Exception;
use ilFormSectionHeaderGUI;

/**
 * Class QuestionHintFormGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\AssessmentQuestion\UserInterface\Web\Form
 */
class QuestionHintFormGUI extends \ilPropertyFormGUI
{

    /**
     * @var Page
     */
    protected $page;
    /**
     * @var QuestionDto
     */
    protected $question_dto;
    /**
     * @var Hint
     */
    protected $hint;


    /**
     * QuestionHintFormGUI constructor.
     *
     * @param Page        $page
     * @param QuestionDto $questionDto
     */
    public function __construct(
        QuestionDto $question_dto,
        Hint $hint
    ) {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        parent::__construct();

        $this->question_dto = $question_dto;
        $this->hint = $hint;

        $this->setTitle($DIC->language()->txt('asq_feedback_form_title'));

        $this->initForm();
    }


    protected function initForm()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $this->setTitle(sprintf($DIC->language()->txt('asq_question_hints_form_header'), $this->question_dto->getData()->getTitle()));

        foreach (Hint::generateField($this->question_dto, $this->hint) as $field) {
            $this->addItem($field);
        }
    }

    /**
     * @return QuestionDto
     * @throws Exception
     */
    public function getQuestion() : QuestionDto {
        $question = $this->question_dto;

        $current_hint = Hint::getValueFromPost();

        $hints_to_save = new Hints();
        $hints_to_save->addHint($current_hint);


        foreach($question->getHints()->getHints() as $hint) {
            if($hint->getOrderNumber() == $current_hint->getOrderNumber()) {
                continue;
            }
            $hints_to_save->addHint($hint);
        }

        $question->setHints($hints_to_save);

        return $question;
    }
}